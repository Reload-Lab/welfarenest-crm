<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Consultazione dei tre registri (modifiche, attivita, accessi).
 *
 * Sola lettura: i log non si creano, non si modificano e non si cancellano
 * da qui. La cancellazione selettiva renderebbe il registro inutile come
 * prova, e l'eventuale politica di retention andra applicata con un comando
 * pianificato, non dall'interfaccia.
 */
class LogController extends Controller
{
    private const PER_PAGE_OPTIONS = [10, 20, 50, 100];

    public function audit(Request $request): View
    {
        $query = AuditLog::query()->with('user');

        $entityType = $request->string('entity_type')->toString();
        $eventType = $request->string('event_type')->toString();
        $search = $request->string('search')->toString();

        $query
            ->when($entityType, fn (Builder $q) => $q->where('auditable_type', $entityType))
            ->when($eventType, fn (Builder $q) => $q->where('event_type', $eventType))
            ->when($search, fn (Builder $q) => $q->where(function (Builder $inner) use ($search) {
                $inner
                    ->where('old_values_json', 'like', "%{$search}%")
                    ->orWhere('new_values_json', 'like', "%{$search}%");
            }));

        $this->applyActorFilter($query, $request);
        $this->applyDateFilter($query, $request);

        return view('logs.audit', [
            'logs' => $this->paginate($query, $request),
            'entityTypes' => $this->entityTypeOptions(),
            'eventTypes' => collect(config('audit_labels.events'))->map(fn ($e) => $e['label'])->all(),
            'users' => $this->userOptions(),
            'filters' => $this->filters($request, ['entity_type', 'event_type', 'search']),
        ]);
    }

    public function show(AuditLog $auditLog): View
    {
        $auditLog->load('user');

        return view('logs.show', [
            'log' => $auditLog,
        ]);
    }

    public function activity(Request $request): View
    {
        $query = ActivityLog::query()->with('user');

        $activityType = $request->string('activity_type')->toString();

        $query->when($activityType, fn (Builder $q) => $q->where('activity_type', $activityType));

        $this->applyActorFilter($query, $request);
        $this->applyDateFilter($query, $request);

        return view('logs.activity', [
            'logs' => $this->paginate($query, $request),
            'activityTypes' => config('audit_labels.activities'),
            'users' => $this->userOptions(),
            'filters' => $this->filters($request, ['activity_type']),
        ]);
    }

    public function access(Request $request): View
    {
        $query = AccessLog::query()->with('user');

        $eventType = $request->string('event_type')->toString();
        $ip = $request->string('ip')->toString();

        $query
            ->when($eventType, fn (Builder $q) => $q->where('event_type', $eventType))
            ->when($ip, fn (Builder $q) => $q->where('ip_address', 'like', "%{$ip}%"));

        $this->applyActorFilter($query, $request);
        $this->applyDateFilter($query, $request);

        return view('logs.access', [
            'logs' => $this->paginate($query, $request),
            'eventTypes' => collect(config('audit_labels.access_events'))->map(fn ($e) => $e['label'])->all(),
            'users' => $this->userOptions(),
            'filters' => $this->filters($request, ['event_type', 'ip']),
        ]);
    }

    // --- Filtri comuni ai tre registri --------------------------------------

    /**
     * "Chi": un utente CRM specifico, oppure tutte le operazioni senza utente
     * (import, comandi, portale WN+, link pubblici).
     */
    private function applyActorFilter(Builder $query, Request $request): void
    {
        $userId = $request->string('user_id')->toString();

        if ($userId === 'none') {
            $query->whereNull('user_id');

            return;
        }

        if (is_numeric($userId)) {
            $query->where('user_id', (int) $userId);
        }
    }

    private function applyDateFilter(Builder $query, Request $request): void
    {
        if ($from = $request->date('from')) {
            $query->where('created_at', '>=', $from->startOfDay());
        }

        if ($to = $request->date('to')) {
            $query->where('created_at', '<=', $to->endOfDay());
        }
    }

    private function paginate(Builder $query, Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);

        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 20;
        }

        $direction = $request->string('direction')->toString() === 'asc' ? 'asc' : 'desc';

        return $query
            ->orderBy('created_at', $direction)
            ->orderBy('id', $direction)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<int, string>  $specific
     * @return array<string, string>
     */
    private function filters(Request $request, array $specific): array
    {
        $keys = array_merge(['user_id', 'from', 'to', 'per_page', 'direction'], $specific);

        return collect($keys)
            ->mapWithKeys(fn (string $key) => [$key => $request->string($key)->toString()])
            ->all();
    }

    /**
     * Solo le entita che compaiono davvero nel registro: un menu a tendina di
     * tipi mai usati non aiuta nessuno.
     *
     * @return array<string, string>
     */
    private function entityTypeOptions(): array
    {
        return AuditLog::query()
            ->select('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type')
            ->mapWithKeys(fn (string $type) => [
                $type => config("audit_labels.entities.{$type}.label", $type),
            ])
            ->all();
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function userOptions()
    {
        return User::query()->orderBy('name')->get(['id', 'name']);
    }
}
