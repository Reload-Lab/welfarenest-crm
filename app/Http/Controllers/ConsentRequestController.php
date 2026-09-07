<?php

namespace App\Http\Controllers;

use App\Models\ConsentRequest;
use App\Models\ContactPoint;
use App\Services\ConsentRequestService;

use Illuminate\Http\RedirectResponse;
use RuntimeException;

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

    /**
     * Invio manuale della richiesta di consenso per un recapito email/PEC di una Persona.
     * Riusa una richiesta "pending" non scaduta già esistente per lo stesso recapito,
     * altrimenti ne crea una nuova. Nessun invio avviene mai in automatico: parte solo
     * quando questa azione viene richiamata esplicitamente dall'utente.
     */
    public function store(ContactPoint $contactPoint): RedirectResponse
    {
        abort_unless($contactPoint->owner_type === 'person', 404);

        $consentRequest = $contactPoint->consentRequests()
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $consentRequest) {
            $consentRequest = app(ConsentRequestService::class)->createForContactPoint($contactPoint);
        }

        try {
            app(ConsentRequestService::class)->send($consentRequest);
        } catch (RuntimeException $e) {
            return redirect()
                ->route('people.show', $contactPoint->owner_id)
                ->withErrors(['consent_request' => $e->getMessage()]);
        }

        return redirect()
            ->route('people.show', $contactPoint->owner_id)
            ->with('success', 'Richiesta di consenso inviata con successo.');
    }
}