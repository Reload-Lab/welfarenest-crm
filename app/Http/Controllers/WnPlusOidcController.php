<?php

namespace App\Http\Controllers;

use App\Models\WnPlusAccount;
use App\Models\WnPlusOidcAuthCode;
use App\Models\WnPlusOidcClient;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;


use Firebase\JWT\JWT;

class WnPlusOidcController extends Controller
{
    public function configuration()
    {
        $issuer = rtrim(config('app.url'), '/');

        return response()->json([
            'issuer' => $issuer,
            'authorization_endpoint' => $issuer . '/wn-plus/oidc/authorize',
            'token_endpoint' => $issuer . '/wn-plus/oidc/token',
            'userinfo_endpoint' => $issuer . '/wn-plus/oidc/userinfo',
            'jwks_uri' => $issuer . '/wn-plus/oidc/jwks',
            'response_types_supported' => ['code'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'scopes_supported' => ['openid', 'profile', 'email'],
            'claims_supported' => [
                'sub',
                'name',
                'given_name',
                'family_name',
                'email',
                'email_verified',
                'wn_role',
                'wn_level',
                'organization_id',
            ],
        ]);
    }

    public function authorize(Request $request)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'string'],
            'redirect_uri' => ['required', 'url'],
            'response_type' => ['required', 'in:code'],
            'scope' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'nonce' => ['nullable', 'string'],
        ]);

        $client = WnPlusOidcClient::query()
            ->where('client_id', $validated['client_id'])
            ->where('is_active', true)
            ->firstOrFail();

        if ($client->redirect_uri !== $validated['redirect_uri']) {
            abort(400, 'redirect_uri non valido.');
        }

        if (! session()->has('wn_plus_account_id')) {
            session([
                'wn_plus_oidc_authorize_request' => $request->fullUrl(),
            ]);

            return redirect()->route('wn-plus.login');
        }

        $account = WnPlusAccount::query()
            ->where('id', session('wn_plus_account_id'))
            ->where('status', 'active')
            ->firstOrFail();

        $authCode = WnPlusOidcAuthCode::create([
            'wn_plus_oidc_client_id' => $client->id,
            'wn_plus_account_id' => $account->id,
            'code' => Str::random(64),
            'redirect_uri' => $validated['redirect_uri'],
            'scope' => $validated['scope'] ?? null,
            'nonce' => $validated['nonce'] ?? null,
            'expires_at' => now()->addMinutes(5),
        ]);

        $querySeparator = str_contains($validated['redirect_uri'], '?') ? '&' : '?';

        $redirectUrl = $validated['redirect_uri'] . $querySeparator . http_build_query(array_filter([
            'code' => $authCode->code,
            'state' => $validated['state'] ?? null,
        ]));

        return redirect()->away($redirectUrl);
    }



    public function token(Request $request)
    {

        $clientId = $request->input('client_id');
        $clientSecret = $request->input('client_secret');

        if (! $clientId && $request->header('Authorization')) {
            $authorization = $request->header('Authorization');

            if (str_starts_with($authorization, 'Basic ')) {
                $decoded = base64_decode(substr($authorization, 6));

                if ($decoded && str_contains($decoded, ':')) {
                    [$clientId, $clientSecret] = explode(':', $decoded, 2);
                }
            }
        }

        $request->merge([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        \Log::info('OIDC token request received', [
            'has_client_id' => (bool) $request->input('client_id'),
            'has_client_secret' => (bool) $request->input('client_secret'),
            'auth_header' => $request->header('Authorization') ? 'present' : 'missing',
        ]);

        $validated = $request->validate([
            'grant_type' => ['required', 'in:authorization_code'],
            'code' => ['required', 'string'],
            'redirect_uri' => ['required', 'url'],
            'client_id' => ['required', 'string'],
            'client_secret' => ['required', 'string'],
        ]);

        $client = WnPlusOidcClient::query()
            ->where('client_id', $validated['client_id'])
            ->where('is_active', true)
            ->first();

        \Log::info('OIDC client check', [
            'client_found' => (bool) $client,
            'request_client_id' => $validated['client_id'],
            'db_client_id' => $client?->client_id,
            'request_secret_len' => strlen($validated['client_secret']),
            'db_secret_len' => strlen((string) $client?->client_secret),
            'request_secret_hash' => substr(hash('sha256', $validated['client_secret']), 0, 12),
            'db_secret_hash' => substr(hash('sha256', (string) $client?->client_secret), 0, 12),
        ]);


        if (! $client || ! hash_equals($client->client_secret, $validated['client_secret'])) {
            return response()->json([
                'error' => 'invalid_client',
            ], 401);
        }

        $authCode = WnPlusOidcAuthCode::query()
            ->with(['account.role', 'account.level'])
            ->where('code', $validated['code'])
            ->whereNull('used_at')
            ->first();

        if (! $authCode || $authCode->expires_at->isPast()) {
            return response()->json([
                'error' => 'invalid_grant',
            ], 400);
        }

        if ((int) $authCode->wn_plus_oidc_client_id !== (int) $client->id) {
            return response()->json([
                'error' => 'invalid_grant',
            ], 400);
        }

        if ($authCode->redirect_uri !== $validated['redirect_uri']) {
            return response()->json([
                'error' => 'invalid_grant',
            ], 400);
        }

        $authCode->update([
            'used_at' => now(),
        ]);

        $account = $authCode->account;

        $accessToken = base64_encode(Str::random(80));

        Cache::put('wn_plus_access_token_' . $accessToken, $account->id, now()->addHour());

        $idToken = $this->makeIdToken($account, $client, $authCode);

        \Log::info('OIDC token generated', [
            'account_id' => $account->id,
            'client_id' => $client->client_id,
            'id_token_starts_with' => substr($idToken, 0, 30),
        ]);

        return response()->json([
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'id_token' => $idToken,
        ]);
    }

    public function jwks()
    {
        $publicKey = file_get_contents(storage_path('app/oidc/public.key'));

        $details = openssl_pkey_get_details(openssl_pkey_get_public($publicKey));

        $modulus = rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '=');
        $exponent = rtrim(strtr(base64_encode($details['rsa']['e']), '+/', '-_'), '=');

        return response()->json([
            'keys' => [
                [
                    'kty' => 'RSA',
                    'use' => 'sig',
                    'kid' => 'wn-plus-oidc-key-1',
                    'alg' => 'RS256',
                    'n' => $modulus,
                    'e' => $exponent,
                ],
            ],
        ]);
    }

    private function makeIdToken($account, $client, $authCode): string
    {
        $issuer = rtrim(config('app.url'), '/');

        $payload = [
            'iss' => $issuer,
            'sub' => $account->uuid,
            'aud' => $client->client_id,
            'iat' => now()->timestamp,
            'exp' => now()->addHour()->timestamp,
            'auth_time' => now()->timestamp,
            'nonce' => $authCode->nonce,

            'name' => $account->full_name,
            'given_name' => $account->first_name,
            'family_name' => $account->last_name,
            'email' => $account->email,
            'email_verified' => (bool) $account->email_verified_at,

            'wn_role' => $account->role?->code,
            'wn_level' => $account->level?->code,
            'organization_id' => $account->organization_id,
        ];

        $privateKey = file_get_contents(storage_path('app/oidc/private.key'));

        return JWT::encode($payload, $privateKey, 'RS256', 'wn-plus-oidc-key-1');
    }


    public function userinfo(Request $request)
    {
        $authorization = $request->header('Authorization');

        if (! $authorization || ! str_starts_with($authorization, 'Bearer ')) {
            return response()->json([
                'error' => 'invalid_token',
            ], 401);
        }

        $token = substr($authorization, 7);

        $accountId = Cache::get('wn_plus_access_token_' . $token);

        if (! $accountId) {
            return response()->json([
                'error' => 'invalid_token',
            ], 401);
        }

        $account = WnPlusAccount::query()
            ->with(['role', 'level'])
            ->where('id', $accountId)
            ->where('status', 'active')
            ->firstOrFail();

        return response()->json([
            'sub' => $account->uuid,
            'name' => $account->full_name,
            'nickname' => $account->full_name,
            'preferred_username' => $account->email,
            'given_name' => $account->first_name,
            'family_name' => $account->last_name,
            'email' => $account->email,
            'email_verified' => (bool) $account->email_verified_at,
            'wn_role' => $account->role?->code,
            'wn_level' => $account->level?->code,
            'organization_id' => $account->organization_id,
        ]);
    }


    public function logout(Request $request)
    {
        $clientId = $request->query('client_id');
        $returnTo = $request->query('returnTo');

        $client = WnPlusOidcClient::query()
            ->where('client_id', $clientId)
            ->where('is_active', true)
            ->first();

        if (! $client) {
            abort(400, 'client_id non valido.');
        }

        \Log::info('OIDC logout richiesto', [
            'client_id' => $clientId,
            'return_to' => $returnTo,
            'had_session' => session()->has('wn_plus_account_id'),
        ]);

        session()->forget('wn_plus_account_id');

        if ($returnTo) {
            $allowedHost = parse_url($client->redirect_uri, PHP_URL_HOST);
            $returnToHost = parse_url($returnTo, PHP_URL_HOST);

            if ($returnToHost && $allowedHost && $returnToHost === $allowedHost) {
                return redirect()->away($returnTo);
            }

            \Log::warning('OIDC logout: returnTo host non corrisponde al client, ignorato', [
                'client_id' => $clientId,
                'return_to_host' => $returnToHost,
                'allowed_host' => $allowedHost,
            ]);
        }

        return redirect()->route('wn-plus.login');
    }


}