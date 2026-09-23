<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\WnPlusAccount;
use App\Support\AccessLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WnPlusAuthController extends Controller
{
    public function showLogin()
    {
        return view('wn-plus.auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $account = WnPlusAccount::where('email', $validated['email'])
            ->where('status', 'active')
            ->first();

        if (! $account || ! Hash::check($validated['password'], $account->password)) {
            AccessLogger::record(AccessLog::EVENT_WN_PLUS_LOGIN_FAILED, null, [
                'email' => $validated['email'],
                'wn_plus_account_id' => $account?->id,
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'Credenziali non valide o account non attivo.',
                ]);
        }

        session([
            'wn_plus_account_id' => $account->id,
        ]);

        $account->update([
            'last_login_at' => now(),
        ]);

        AccessLogger::record(AccessLog::EVENT_WN_PLUS_LOGIN, null, [
            'wn_plus_account_id' => $account->id,
            'email' => $account->email,
        ]);

        $intendedOidcUrl = session()->pull('wn_plus_oidc_authorize_request');

        if ($intendedOidcUrl) {
            return redirect()->away($intendedOidcUrl);
        }

        $oidcAuthorizeUrl = session()->pull('wn_plus_oidc_authorize_request');

        if ($oidcAuthorizeUrl) {
            return redirect()->to($oidcAuthorizeUrl);
        }

        return redirect()->route('wn-plus.portal.dashboard');
    }

    public function logout(Request $request)
    {
        AccessLogger::record(AccessLog::EVENT_WN_PLUS_LOGOUT, null, array_filter([
            'wn_plus_account_id' => $request->session()->get('wn_plus_account_id'),
        ]));

        $request->session()->forget('wn_plus_account_id');

        return redirect()->route('wn-plus.login');
    }
}