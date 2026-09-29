<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autenticazione degli endpoint consumati dal sito WN+.
 *
 * Token condiviso in header Authorization: Bearer <token>, piu' un'allowlist di
 * IP opzionale. Confronto con hash_equals per non esporre la differenza nei
 * tempi di risposta.
 *
 * Il token assente in configurazione NON apre l'accesso: chiude l'endpoint con
 * 503. Un'API che espone email e consensi di tutti gli account non deve poter
 * diventare pubblica per una variabile d'ambiente dimenticata in un deploy.
 */
class VerifyWnPlusApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.wn_plus_api.token');

        if ($expected === '') {
            return response()->json([
                'message' => 'Endpoint non configurato.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $allowedIps = config('services.wn_plus_api.allowed_ips', []);

        if (! empty($allowedIps) && ! in_array($request->ip(), $allowedIps, true)) {
            return response()->json([
                'message' => 'Non autorizzato.',
            ], Response::HTTP_FORBIDDEN);
        }

        $provided = (string) $request->bearerToken();

        if ($provided === '' || ! hash_equals($expected, $provided)) {
            return response()->json([
                'message' => 'Non autorizzato.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
