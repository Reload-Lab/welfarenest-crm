<?php

namespace App\Http\Controllers;

use App\Models\ConsentRequest;
use App\Models\ContactPoint;
use App\Models\Person;
use App\Services\ConsentRequestService;
use App\Services\ConsentService;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ConsentRequestController extends Controller
{
    public function show(string $token)
    {
        $consentRequest = ConsentRequest::query()
            ->with(['contactPoint', 'items.consentType', 'items.consentVersion'])
            ->where('token', $token)
            ->firstOrFail();

        if ($consentRequest->status === 'completed') {
            return view('consent-requests.completed');
        }

        if ($consentRequest->status !== 'pending' || $consentRequest->expires_at->isPast()) {
            abort(410, 'Richiesta consenso scaduta o non più valida.');
        }

        // Il form pubblico separa visivamente gli item obbligatori da quelli
        // facoltativi: la partizione sta qui e non in Blade, così la vista si
        // limita a ciclare due collezioni già ordinate.
        $items = $consentRequest->items->sortBy('sort_order');

        return view('consent-requests.show', [
            'consentRequest' => $consentRequest,
            'requiredItems' => $items->where('is_required', true)->values(),
            'optionalItems' => $items->where('is_required', false)->values(),
        ]);
    }

    /**
     * Registra la decisione del destinatario per ciascun item della richiesta.
     * Un item obbligatorio non spuntato blocca l'invio (nessuna scrittura,
     * si torna al form con un errore). Un item facoltativo non spuntato viene
     * registrato esplicitamente come "denied", non lasciato in sospeso: chi
     * gestisce il CRM deve poter distinguere "ha rifiutato" da "non ha ancora
     * risposto" — quest'ultimo caso è la richiesta ancora 'pending'.
     */
    public function complete(Request $request, string $token, ConsentService $consentService): RedirectResponse
    {
        $consentRequest = ConsentRequest::query()
            ->with('items.consentType', 'items.consentVersion')
            ->where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($consentRequest->expires_at->isPast()) {
            abort(410, 'Richiesta consenso scaduta.');
        }

        foreach ($consentRequest->items as $item) {
            if ($item->is_required && ! $request->boolean('consent_' . $item->consent_type_id)) {
                return back()->withErrors([
                    'consents' => 'Devi confermare la presa visione dell\'informativa per proseguire.',
                ]);
            }
        }

        DB::transaction(function () use ($consentRequest, $request, $consentService) {
            foreach ($consentRequest->items as $item) {
                $granted = $request->boolean('consent_' . $item->consent_type_id);

                $method = $granted ? 'grant' : 'deny';

                $consentService->{$method}(
                    $consentRequest->owner_type,
                    $consentRequest->owner_id,
                    $item->consentType->code,
                    'email_consent_request',
                    $item->consentVersion?->version_code
                );
            }

            $consentRequest->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        });

        return redirect()->route('consent-requests.show', $token);
    }

    /**
     * Espone il PDF dell'informativa collegata a un item della richiesta.
     * Autorizzazione tramite il token stesso (nessun login pubblico): si
     * può scaricare solo un documento effettivamente proposto in quella
     * specifica richiesta, non un consent_version_id arbitrario.
     */
    public function document(string $token, int $consentVersionId)
    {
        $consentRequest = ConsentRequest::where('token', $token)->firstOrFail();

        $item = $consentRequest->items()
            ->where('consent_version_id', $consentVersionId)
            ->with('consentVersion')
            ->firstOrFail();

        $path = $item->consentVersion?->content_file_path;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    /**
     * Invio manuale della richiesta di consenso per un recapito email/PEC di una Persona.
     * Riusa una richiesta "pending" non scaduta già esistente per lo stesso recapito,
     * altrimenti ne crea una nuova. Nessun invio avviene mai in automatico: parte solo
     * quando questa azione viene richiamata esplicitamente dall'utente.
     *
     * Stesso metodo usato anche come "Reinvia" dalla dashboard Consensi: se la
     * richiesta precedente è ancora valida ne rispedisce il link, altrimenti
     * ne genera una nuova con gli item di default.
     */
    public function store(ContactPoint $contactPoint): RedirectResponse
    {
        abort_unless($contactPoint->owner_type === 'person', 404);

        return $this->sendConsentRequestForContactPoint($contactPoint);
    }

    /**
     * Invio della richiesta di consenso a livello di Persona (semplificazione
     * concordata con la DPO): non si sceglie più il recapito, si usa sempre
     * l'email primaria della persona o, in mancanza, la prima email disponibile.
     * Un'unica azione sulla scheda persona, un solo indirizzo di riferimento.
     */
    public function storeForPerson(Person $person): RedirectResponse
    {
        $contactPoint = $person->primaryOrFirstEmailContactPoint();

        if (! $contactPoint) {
            return redirect()
                ->route('people.show', $person)
                ->with('openConsentsModal', true)
                ->withErrors(['consent_request' => 'La persona non ha un indirizzo email a cui inviare la richiesta di consenso.']);
        }

        return $this->sendConsentRequestForContactPoint($contactPoint);
    }

    private function sendConsentRequestForContactPoint(ContactPoint $contactPoint): RedirectResponse
    {
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
                ->with('openConsentsModal', true)
                ->withErrors(['consent_request' => $e->getMessage()]);
        }

        // openConsentsModal: l'azione parte da dentro la modale "Privacy e
        // consensi", quindi dopo il redirect la riapriamo sullo stato aggiornato.
        return redirect()
            ->route('people.show', $contactPoint->owner_id)
            ->with('openConsentsModal', true)
            ->with('success', 'Richiesta di consenso inviata con successo.');
    }
}
