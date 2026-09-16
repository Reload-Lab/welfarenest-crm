@php
    use App\Models\ConsentType;

    // Con una ricerca attiva, i gruppi rilevanti partono già aperti: altrimenti
    // chi cerca un utente dovrebbe comunque cliccare la freccina per vederlo.
    $forceExpanded = ($search ?? '') !== '';
@endphp

<div class="card border-0 shadow-sm">

    <div class="card-header bg-white border-0 d-flex justify-content-end">
        <button type="button"
                id="wnplusToggleAll"
                class="btn btn-sm btn-outline-secondary"
                data-state="{{ $forceExpanded ? 'expanded' : 'collapsed' }}">
            {{ $forceExpanded ? 'Comprimi tutti' : 'Espandi tutti' }}
        </button>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table crm-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Utente</th>
                        <th>Organizzazione</th>
                        <th>Ruolo</th>
                        <th>Consensi</th>
                        <th>Stato</th>
                        <th>Ultimo accesso</th>
                        <th class="text-end">Azioni</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($managers as $manager)
                        @php
                            $managedUsers = $manager->invitedAccounts;
                            $groupId = 'mgr-' . $manager->id;
                            $groupExpanded = $forceExpanded && $managedUsers->isNotEmpty();
                        @endphp

                        <tr>
                            <td>
                                <div class="d-flex align-items-start gap-2">
                                    @if($managedUsers->isNotEmpty())
                                        <button type="button"
                                                class="btn btn-icon crm-wnplus-toggle"
                                                data-wnplus-toggle="{{ $groupId }}"
                                                aria-expanded="{{ $groupExpanded ? 'true' : 'false' }}"
                                                title="Mostra/nascondi utenti"
                                                aria-label="Mostra/nascondi utenti">
                                            <x-icon group="actions" name="chevron-right" class="crm-wnplus-toggle-icon" />
                                        </button>
                                    @else
                                        <span class="crm-wnplus-toggle-spacer" aria-hidden="true"></span>
                                    @endif

                                    <x-crm.avatar :name="$manager->full_name" type="person" size="sm" />

                                    <div>
                                        <div class="fw-semibold">
                                            {{ $manager->full_name }}

                                            @if($managedUsers->isNotEmpty())
                                                <span class="text-muted small fw-normal">
                                                    ({{ $managedUsers->count() }} {{ $managedUsers->count() === 1 ? 'utente' : 'utenti' }})
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-muted small">
                                            {{ $manager->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                @if($manager->organization)
                                    <a href="{{ route('organizations.show', $manager->organization) }}" class="d-flex align-items-center gap-2 text-decoration-none">
                                        <x-crm.avatar :name="$manager->organization->name ?? $manager->organization->legal_name" type="organization" size="sm" />
                                        <span>{{ $manager->organization->name ?? $manager->organization->legal_name }}</span>
                                    </a>
                                @else
                                    —
                                @endif
                            </td>

                            <td>
                                <x-crm.tag label="Referente" variant="primary" />
                            </td>

                            <td>
                                <button
                                    type="button"
                                    class="crm-status-badge crm-status-badge--{{ $manager->consentBadgeVariant(ConsentType::PRIVACY_NOTICE) }} border-0"
                                    title="{{ $manager->consentStatusLabel(ConsentType::PRIVACY_NOTICE) }}"
                                    aria-label="{{ $manager->consentStatusLabel(ConsentType::PRIVACY_NOTICE) }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#wnplusConsentsModal{{ $manager->id }}">
                                    <x-icon group="entities" name="consent" />
                                </button>
                            </td>

                            <td>
                                <x-crm.status
                                    :label="$manager->statusLabel()"
                                    :variant="$manager->statusBadgeVariant()"
                                    icon-group="status"
                                    :icon-name="$manager->statusIcon()"
                                    mode="icon"
                                />
                            </td>

                            <td>
                                {{ $manager->last_login_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>

                            <td class="text-end">

                                @include('components.crm.row-actions', [
                                    'view' => route('wn-plus.accounts.show', $manager),
                                    'edit' => route('wn-plus.accounts.edit', $manager),
                                    'delete' => route('wn-plus.accounts.destroy', $manager),
                                    'deleteConfirm' => 'Confermi l\'eliminazione di questo account WN+?',

                                    'actions' => [
                                        [
                                            'label' => 'Genera invito',
                                            'route' => route('wn-plus.accounts.invite', $manager),
                                            'method' => 'POST',
                                            'icon' => 'send',
                                            'show' => $manager->status !== 'active',
                                        ],
                                        [
                                            'route' => route('wn-plus.accounts.suspend', $manager),
                                            'label' => 'Sospendi account',
                                            'icon' => 'archive',
                                            'show' => ! in_array($manager->status, ['suspended', 'disabled'], true),
                                        ],
                                        [
                                            'route' => route('wn-plus.accounts.reactivate', $manager),
                                            'label' => 'Riattiva account',
                                            'icon' => 'archive-restore',
                                            'show' => in_array($manager->status, ['suspended', 'disabled'], true),
                                        ],
                                        [
                                            'route' => route('wn-plus.accounts.disable', $manager),
                                            'label' => 'Disabilita account',
                                            'icon' => 'close',
                                            'show' => $manager->status !== 'disabled',
                                        ],
                                    ],
                                ])

                            </td>
                        </tr>

                        @foreach($managedUsers as $user)
                            <tr class="crm-table__row--wnplus-user {{ $groupExpanded ? '' : 'd-none' }}" data-wnplus-group="{{ $groupId }}">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <x-crm.avatar :name="$user->full_name" type="person" size="sm" />

                                        <div>
                                            <div class="fw-semibold">
                                                {{ $user->full_name }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ $user->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if($user->organization)
                                        <a href="{{ route('organizations.show', $user->organization) }}" class="d-flex align-items-center gap-2 text-decoration-none">
                                            <x-crm.avatar :name="$user->organization->name ?? $user->organization->legal_name" type="organization" size="sm" />
                                            <span>{{ $user->organization->name ?? $user->organization->legal_name }}</span>
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td>
                                    <x-crm.tag label="Utente" variant="default" />
                                </td>

                                <td>
                                    <button
                                        type="button"
                                        class="crm-status-badge crm-status-badge--{{ $user->consentBadgeVariant(ConsentType::PRIVACY_NOTICE) }} border-0"
                                        title="{{ $user->consentStatusLabel(ConsentType::PRIVACY_NOTICE) }}"
                                        aria-label="{{ $user->consentStatusLabel(ConsentType::PRIVACY_NOTICE) }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#wnplusConsentsModal{{ $user->id }}">
                                        <x-icon group="entities" name="consent" />
                                    </button>
                                </td>

                                <td>
                                    <x-crm.status
                                        :label="$user->statusLabel()"
                                        :variant="$user->statusBadgeVariant()"
                                        icon-group="status"
                                        :icon-name="$user->statusIcon()"
                                        mode="icon"
                                    />
                                </td>

                                <td>
                                    {{ $user->last_login_at?->format('d/m/Y H:i') ?? '—' }}
                                </td>

                                <td class="text-end">

                                    @include('components.crm.row-actions', [
                                        'view' => route('wn-plus.accounts.show', $user),
                                        'edit' => route('wn-plus.accounts.edit', $user),
                                        'delete' => route('wn-plus.accounts.destroy', $user),
                                        'deleteConfirm' => 'Confermi l\'eliminazione di questo account WN+?',

                                        'actions' => [
                                            [
                                                'label' => 'Genera invito',
                                                'route' => route('wn-plus.accounts.invite', $user),
                                                'method' => 'POST',
                                                'icon' => 'send',
                                                'show' => $user->status !== 'active',
                                            ],
                                            [
                                                'route' => route('wn-plus.accounts.suspend', $user),
                                                'label' => 'Sospendi account',
                                                'icon' => 'archive',
                                                'show' => ! in_array($user->status, ['suspended', 'disabled'], true),
                                            ],
                                            [
                                                'route' => route('wn-plus.accounts.reactivate', $user),
                                                'label' => 'Riattiva account',
                                                'icon' => 'archive-restore',
                                                'show' => in_array($user->status, ['suspended', 'disabled'], true),
                                            ],
                                            [
                                                'route' => route('wn-plus.accounts.disable', $user),
                                                'label' => 'Disabilita account',
                                                'icon' => 'close',
                                                'show' => $user->status !== 'disabled',
                                            ],
                                        ],
                                    ])

                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                Nessun utente WN+ presente.
                            </td>
                        </tr>
                    @endforelse

                    @if($orphanUsers->isNotEmpty())
                        <tr class="crm-wnplus-group-divider">
                            <td colspan="7">
                                Utenti senza referente assegnato
                            </td>
                        </tr>

                        @foreach($orphanUsers as $user)
                            <tr class="crm-table__row--wnplus-user">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <x-crm.avatar :name="$user->full_name" type="person" size="sm" />

                                        <div>
                                            <div class="fw-semibold">
                                                {{ $user->full_name }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ $user->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if($user->organization)
                                        <a href="{{ route('organizations.show', $user->organization) }}" class="d-flex align-items-center gap-2 text-decoration-none">
                                            <x-crm.avatar :name="$user->organization->name ?? $user->organization->legal_name" type="organization" size="sm" />
                                            <span>{{ $user->organization->name ?? $user->organization->legal_name }}</span>
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td>
                                    <x-crm.tag label="Utente" variant="default" />
                                </td>

                                <td>
                                    <button
                                        type="button"
                                        class="crm-status-badge crm-status-badge--{{ $user->consentBadgeVariant(ConsentType::PRIVACY_NOTICE) }} border-0"
                                        title="{{ $user->consentStatusLabel(ConsentType::PRIVACY_NOTICE) }}"
                                        aria-label="{{ $user->consentStatusLabel(ConsentType::PRIVACY_NOTICE) }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#wnplusConsentsModal{{ $user->id }}">
                                        <x-icon group="entities" name="consent" />
                                    </button>
                                </td>

                                <td>
                                    <x-crm.status
                                        :label="$user->statusLabel()"
                                        :variant="$user->statusBadgeVariant()"
                                        icon-group="status"
                                        :icon-name="$user->statusIcon()"
                                        mode="icon"
                                    />
                                </td>

                                <td>
                                    {{ $user->last_login_at?->format('d/m/Y H:i') ?? '—' }}
                                </td>

                                <td class="text-end">

                                    @include('components.crm.row-actions', [
                                        'view' => route('wn-plus.accounts.show', $user),
                                        'edit' => route('wn-plus.accounts.edit', $user),
                                        'delete' => route('wn-plus.accounts.destroy', $user),
                                        'deleteConfirm' => 'Confermi l\'eliminazione di questo account WN+?',

                                        'actions' => [
                                            [
                                                'label' => 'Genera invito',
                                                'route' => route('wn-plus.accounts.invite', $user),
                                                'method' => 'POST',
                                                'icon' => 'send',
                                                'show' => $user->status !== 'active',
                                            ],
                                            [
                                                'route' => route('wn-plus.accounts.suspend', $user),
                                                'label' => 'Sospendi account',
                                                'icon' => 'archive',
                                                'show' => ! in_array($user->status, ['suspended', 'disabled'], true),
                                            ],
                                            [
                                                'route' => route('wn-plus.accounts.reactivate', $user),
                                                'label' => 'Riattiva account',
                                                'icon' => 'archive-restore',
                                                'show' => in_array($user->status, ['suspended', 'disabled'], true),
                                            ],
                                            [
                                                'route' => route('wn-plus.accounts.disable', $user),
                                                'label' => 'Disabilita account',
                                                'icon' => 'close',
                                                'show' => $user->status !== 'disabled',
                                            ],
                                        ],
                                    ])

                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{--
        Le modali dei consensi vivono qui, fuori dalla tabella: dentro il <tbody>
        (tra una riga e l'altra) non sono HTML valido e il browser le "espelle"
        dalla tabella in modo scorretto, facendole comparire come contenuto in
        chiaro invece che come popup nascosto.
    --}}
    @php
        $wnplusModalAccounts = collect();

        foreach ($managers as $manager) {
            $wnplusModalAccounts->push($manager);

            foreach ($manager->invitedAccounts as $invitedAccount) {
                $wnplusModalAccounts->push($invitedAccount);
            }
        }

        foreach ($orphanUsers as $orphanUser) {
            $wnplusModalAccounts->push($orphanUser);
        }
    @endphp

    @foreach($wnplusModalAccounts as $modalAccount)
        @include('people.partials.show.consents-modal', [
            'modalId' => 'wnplusConsentsModal' . $modalAccount->id,
            'owner' => $modalAccount,
        ])
    @endforeach

    @if($managers->hasPages())
        <div class="card-footer bg-white border-0">
            {{ $managers->links() }}
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggleButtons = document.querySelectorAll('.crm-wnplus-toggle');

        function setGroupState(button, expanded) {
            var groupId = button.dataset.wnplusToggle;

            document.querySelectorAll('[data-wnplus-group="' + groupId + '"]').forEach(function (row) {
                row.classList.toggle('d-none', !expanded);
            });

            button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }

        toggleButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var expanded = button.getAttribute('aria-expanded') === 'true';
                setGroupState(button, !expanded);
            });
        });

        var toggleAllButton = document.getElementById('wnplusToggleAll');

        if (toggleAllButton && toggleButtons.length) {
            toggleAllButton.addEventListener('click', function () {
                var shouldExpand = toggleAllButton.dataset.state !== 'expanded';

                toggleButtons.forEach(function (button) {
                    setGroupState(button, shouldExpand);
                });

                toggleAllButton.textContent = shouldExpand ? 'Comprimi tutti' : 'Espandi tutti';
                toggleAllButton.dataset.state = shouldExpand ? 'expanded' : 'collapsed';
            });
        } else if (toggleAllButton) {
            // Nessun referente ha utenti da mostrare/nascondere: il pulsante non serve.
            toggleAllButton.classList.add('d-none');
        }
    });
</script>
