@php
    $allGroups = collect($groups);

    if ($unassignedGroup) {
        $allGroups = $allGroups->push($unassignedGroup);
    }

    // Con una ricerca attiva i gruppi partono già aperti fino in fondo: altrimenti
    // chi cerca una persona dovrebbe comunque cliccare le freccine per vederla.
    $forceExpanded = ($search ?? '') !== '';

    // Tutti gli account a video, in ordine: servono per generare le modali dei consensi.
    $modalAccounts = collect();

    foreach ($allGroups as $group) {
        foreach ($group['managers'] as $managerRow) {
            $modalAccounts->push($managerRow['manager']);

            foreach ($managerRow['users'] as $user) {
                $modalAccounts->push($user);
            }
        }

        foreach ($group['looseUsers'] as $user) {
            $modalAccounts->push($user);
        }
    }
@endphp

<div class="crm-table-card">

    <div class="crm-table-card__header d-flex justify-content-between align-items-center gap-3">
        <div>
            <h2 class="crm-table-card__title">Account per organizzazione</h2>
            <p class="crm-table-card__subtitle mb-0">
                Ogni organizzazione raccoglie i propri referenti; gli utenti invitati
                stanno sotto al referente che li ha creati.
            </p>
        </div>

        <button type="button"
                id="wnplusToggleAll"
                class="btn btn-outline-secondary btn-inline"
                data-state="{{ $forceExpanded ? 'expanded' : 'collapsed' }}">
            {{ $forceExpanded ? 'Comprimi tutti' : 'Espandi tutti' }}
        </button>
    </div>

    <div class="crm-table-responsive">
        <table class="table crm-table align-middle mb-0">
            <thead>
                <tr>
                    {{-- La tabella è annidata, quindi gli ordinamenti sono due:
                         "Organizzazione" ordina i gruppi, "Utente" ordina le persone
                         dentro ciascun gruppo. --}}
                    <th class="crm-cell-start">
                        <span class="crm-wnplus-sort-pair">
                            @include('components.crm.sortable-th', [
                                'label' => 'Organizzazione',
                                'field' => 'organization',
                                'defaultSort' => 'organization',
                            ])

                            <span class="crm-wnplus-sort-sep" aria-hidden="true">/</span>

                            @include('components.crm.sortable-th', [
                                'label' => 'Utente',
                                'field' => 'name',
                                'defaultSort' => 'organization',
                            ])
                        </span>
                    </th>

                    <th>Ruolo</th>
                    <th class="text-center">Consensi</th>
                    <th class="text-center">Stato</th>
                    <th>Ultimo accesso</th>
                    <th class="text-end crm-cell-end">Azioni</th>
                </tr>
            </thead>

            <tbody>
                @foreach($groups as $group)
                    @include('wn-plus.accounts.partials.group', ['group' => $group])
                @endforeach

                @if($unassignedGroup)
                    @include('wn-plus.accounts.partials.group', ['group' => $unassignedGroup])
                @endif

                @if($allGroups->isEmpty())
                    <tr>
                        <td colspan="6" class="p-0">
                            <div class="crm-empty-state">
                                <div class="crm-empty-state__icon">
                                    <x-icon group="actions" name="search" />
                                </div>
                                <h3 class="crm-empty-state__title">Nessun utente WN+ trovato</h3>
                                <p class="crm-empty-state__text">
                                    Non ci sono account da mostrare con i filtri correnti.
                                </p>

                                <div class="mt-3">
                                    <x-crm.button
                                        href="{{ route('wn-plus.accounts.create') }}"
                                        icon="add"
                                        variant="primary"
                                    >
                                        Crea il primo referente
                                    </x-crm.button>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="card-footer crm-table-footer">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <form method="GET" action="{{ route('wn-plus.accounts.index') }}" class="crm-table-footer__left">
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">

                <select
                    name="per_page"
                    id="per_page_footer"
                    class="form-select form-select-sm"
                    onchange="this.form.submit()"
                >
                    <option value="10" {{ (int) $perPage === 10 ? 'selected' : '' }}>10 organizzazioni</option>
                    <option value="20" {{ (int) $perPage === 20 ? 'selected' : '' }}>20 organizzazioni</option>
                    <option value="50" {{ (int) $perPage === 50 ? 'selected' : '' }}>50 organizzazioni</option>
                </select>
            </form>

            <div class="crm-table-footer__right">
                @if($organizations->hasPages())
                    <div class="crm-pagination">
                        {{ $organizations->links() }}
                    </div>
                @else
                    <span class="crm-text-muted small">
                        {{ $organizations->total() }}
                        {{ $organizations->total() === 1 ? 'organizzazione trovata' : 'organizzazioni trovate' }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

{{--
    Le modali dei consensi vivono qui, fuori dalla tabella: dentro il <tbody>
    (tra una riga e l'altra) non sono HTML valido e il browser le "espelle"
    dalla tabella in modo scorretto, facendole comparire come contenuto in
    chiaro invece che come popup nascosto.
--}}
@foreach($modalAccounts as $modalAccount)
    @include('people.partials.show.consents-modal', [
        'modalId' => 'wnplusConsentsModal' . $modalAccount->id,
        'owner' => $modalAccount,
    ])
@endforeach

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var forceExpanded = @json($forceExpanded);

        // Due livelli di apertura indipendenti: l'organizzazione mostra o nasconde
        // tutto il proprio blocco, il referente mostra o nasconde i suoi utenti.
        // Gli stati vivono qui e la visibilità delle righe viene sempre ricalcolata
        // da entrambi, così riaprendo un'organizzazione i referenti chiusi restano chiusi.
        var orgState = {};
        var managerState = {};

        document.querySelectorAll('[data-wnplus-org-toggle]').forEach(function (button) {
            orgState[button.dataset.wnplusOrgToggle] = true;
        });

        document.querySelectorAll('[data-wnplus-toggle]').forEach(function (button) {
            managerState[button.dataset.wnplusToggle] = forceExpanded;
        });

        function refresh() {
            document.querySelectorAll('[data-wnplus-row]').forEach(function (row) {
                var orgOpen = orgState[row.dataset.wnplusOrg] !== false;
                var managerKey = row.dataset.wnplusManager;
                var managerOpen = !managerKey || managerState[managerKey] === true;

                row.classList.toggle('d-none', !(orgOpen && managerOpen));
            });

            document.querySelectorAll('[data-wnplus-org-toggle]').forEach(function (button) {
                button.setAttribute(
                    'aria-expanded',
                    orgState[button.dataset.wnplusOrgToggle] !== false ? 'true' : 'false'
                );
            });

            document.querySelectorAll('[data-wnplus-toggle]').forEach(function (button) {
                button.setAttribute(
                    'aria-expanded',
                    managerState[button.dataset.wnplusToggle] === true ? 'true' : 'false'
                );
            });
        }

        document.querySelectorAll('[data-wnplus-org-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                var key = button.dataset.wnplusOrgToggle;
                orgState[key] = orgState[key] === false;
                refresh();
            });
        });

        document.querySelectorAll('[data-wnplus-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                var key = button.dataset.wnplusToggle;
                managerState[key] = managerState[key] !== true;
                refresh();
            });
        });

        var toggleAllButton = document.getElementById('wnplusToggleAll');

        if (toggleAllButton) {
            toggleAllButton.addEventListener('click', function () {
                var shouldExpand = toggleAllButton.dataset.state !== 'expanded';

                Object.keys(orgState).forEach(function (key) {
                    orgState[key] = shouldExpand;
                });

                Object.keys(managerState).forEach(function (key) {
                    managerState[key] = shouldExpand;
                });

                toggleAllButton.textContent = shouldExpand ? 'Comprimi tutti' : 'Espandi tutti';
                toggleAllButton.dataset.state = shouldExpand ? 'expanded' : 'collapsed';

                refresh();
            });
        }

        refresh();
    });
</script>
