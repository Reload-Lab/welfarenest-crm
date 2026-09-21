<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\User;

use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $statusId = $request->input('lead_status_id');
        $sourceId = $request->input('lead_source_id');
        $assignedUserId = $request->input('assigned_user_id');
        $onlyOpen = $request->boolean('only_open');
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
        $perPage = (int) $request->input('per_page', 20);

        $allowedSorts = [
            'name',
            'last_name',
            'company_name',
            'expected_close_date',
            'estimated_value',
            'created_at',
        ];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        if (! in_array($perPage, [10, 20, 50], true)) {
            $perPage = 20;
        }

        $leads = Lead::query()
            ->with(['status', 'source', 'assignedUser', 'organization', 'person'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            })
            ->when(filled($statusId), fn ($query) => $query->where('lead_status_id', $statusId))
            ->when(filled($sourceId), fn ($query) => $query->where('lead_source_id', $sourceId))
            ->when(filled($assignedUserId), fn ($query) => $query->where('assigned_user_id', $assignedUserId))
            ->when($onlyOpen, fn ($query) => $query->open())
            ->orderBy($sort, $direction)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('leads.index', [
            'leads' => $leads,
            'search' => $search,
            'statusId' => $statusId,
            'sourceId' => $sourceId,
            'assignedUserId' => $assignedUserId,
            'onlyOpen' => $onlyOpen,
            'sort' => $sort,
            'direction' => $direction,
            'perPage' => $perPage,
            'statuses' => $this->activeStatuses(),
            'sources' => $this->activeSources(),
            'users' => $this->users(),
        ]);
    }

    public function create()
    {
        return view('leads.create', [
            'statuses' => $this->activeStatuses(),
            'sources' => $this->activeSources(),
            'users' => $this->users(),
            'defaultStatusId' => $this->defaultStatusId(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $lead = new Lead($validated);
        $this->applyStatusSideEffects($lead);
        $lead->save();

        return redirect()
            ->route('leads.show', $lead)
            ->with('success', 'Lead creato con successo.');
    }

    public function show(Lead $lead)
    {
        $lead->load([
            'status',
            'source',
            'assignedUser',
            'convertedByUser',
            'organization',
            'person',
        ]);

        return view('leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        return view('leads.edit', [
            'lead' => $lead,
            'statuses' => $this->activeStatuses($lead->lead_status_id),
            'sources' => $this->activeSources($lead->lead_source_id),
            'users' => $this->users(),
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $this->validated($request);

        $lead->fill($validated);
        $this->applyStatusSideEffects($lead);
        $lead->save();

        return redirect()
            ->route('leads.show', $lead)
            ->with('success', 'Lead aggiornato con successo.');
    }

    public function destroy(Lead $lead)
    {
        // Un lead convertito è la traccia di come è nata un'anagrafica:
        // eliminarlo fa perdere quel collegamento in modo irreversibile.
        if ($lead->isConverted()) {
            return back()->with(
                'error',
                'Questo lead è stato convertito in anagrafica e non può essere eliminato. Disattivalo, se necessario.'
            );
        }

        $lead->delete();

        return redirect()
            ->route('leads.index')
            ->with('success', 'Lead eliminato con successo.');
    }

    protected function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'lead_status_id' => ['required', 'integer', 'exists:lead_statuses,id'],
            'lead_source_id' => ['nullable', 'integer', 'exists:lead_sources,id'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'expected_close_date' => ['nullable', 'date'],
            'lost_reason' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ], [], [
            'name' => 'titolo',
            'lead_status_id' => 'stato',
            'lead_source_id' => 'fonte',
            'assigned_user_id' => 'assegnatario',
            'estimated_value' => 'valore stimato',
            'expected_close_date' => 'data prevista di chiusura',
            'lost_reason' => 'motivo della perdita',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    /**
     * Allinea le date di chiusura allo stato scelto. Il motivo della perdita
     * ha senso solo su uno stato perso: se il lead torna in lavorazione,
     * chiusura e motivo vengono azzerati invece di restare come residuo.
     */
    protected function applyStatusSideEffects(Lead $lead): void
    {
        $status = LeadStatus::find($lead->lead_status_id);

        if (! $status) {
            return;
        }

        if ($status->is_final) {
            $lead->closed_at = $lead->closed_at ?? now();

            if (! $status->is_lost) {
                $lead->lost_reason = null;
            }

            return;
        }

        $lead->closed_at = null;
        $lead->lost_reason = null;
    }

    /**
     * Gli stati disattivati restano visibili solo se già assegnati al lead
     * che si sta modificando, per non falsare il valore salvato.
     */
    protected function activeStatuses(?int $keepId = null)
    {
        return LeadStatus::query()
            ->where(fn ($query) => $query->where('is_active', true)->when($keepId, fn ($q) => $q->orWhere('id', $keepId)))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    protected function activeSources(?int $keepId = null)
    {
        return LeadSource::query()
            ->where(fn ($query) => $query->where('is_active', true)->when($keepId, fn ($q) => $q->orWhere('id', $keepId)))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    protected function users()
    {
        return User::query()->orderBy('name')->get(['id', 'name']);
    }

    protected function defaultStatusId(): ?int
    {
        return LeadStatus::query()
            ->where('is_active', true)
            ->open()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->value('id');
    }
}
