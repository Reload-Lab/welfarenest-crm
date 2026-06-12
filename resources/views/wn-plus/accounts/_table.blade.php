<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table crm-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Utente</th>
                        <th>Organizzazione</th>
                        <th>Ruolo</th>
                        <th>Livello</th>
                        <th>Stato</th>
                        <th>Ultimo accesso</th>
                        <th class="text-end">Azioni</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    {{ $account->full_name }}
                                </div>
                                <div class="text-muted small">
                                    {{ $account->email }}
                                </div>
                            </td>

                            <td>
                                {{ $account->organization?->name ?? $account->organization?->legal_name ?? '—' }}
                            </td>

                            <td>
                                <span class="crm-status-badge">
                                    {{ $account->role?->name ?? '—' }}
                                </span>
                            </td>

                            <td>
                                <span class="crm-status-badge">
                                    {{ $account->level?->name ?? '—' }}
                                </span>
                            </td>

                            <td>
                                <span class="crm-status-badge">
                                    {{ ucfirst($account->status) }}
                                </span>
                            </td>

                            <td>
                                {{ $account->last_login_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>

                            <td class="text-end">

                                @include('components.crm.row-actions', [
                                    'view' => route('wn-plus.accounts.show', $account),
                                    'edit' => route('wn-plus.accounts.edit', $account),
                                    'delete' => route('wn-plus.accounts.destroy', $account),
                                    'deleteConfirm' => 'Confermi l\'eliminazione di questo account WN+?',

                                    'actions' => [
                                        [
                                            'label' => 'Genera invito',
                                            'route' => route('wn-plus.accounts.invite', $account),
                                            'method' => 'POST',
                                            'icon' => 'send',
                                            'show' => $account->status !== 'active',
                                        ],
                                    ],
                                ])

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                Nessun utente WN+ presente.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($accounts->hasPages())
        <div class="card-footer bg-white border-0">
            {{ $accounts->links() }}
        </div>
    @endif
</div>