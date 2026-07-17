<?php

namespace App\Http\Controllers;

use App\Models\ConsentRequest;

class ConsentRequestController extends Controller
{
    public function show(string $token)
    {
        $consentRequest = ConsentRequest::query()
            ->with(['contactPoint'])
            ->where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($consentRequest->expires_at->isPast()) {
            abort(410, 'Richiesta consenso scaduta.');
        }

        return view('consent-requests.show', compact('consentRequest'));
    }
}