@php
    $organizationsForJs = $organizations->map(function ($organization) {
        return [
            'id' => $organization->id,
            'name' => $organization->name ?? $organization->legal_name,
        ];
    })->values();

    $relationsForJs = $organizations->flatMap(function ($organization) {
        return $organization->personRelations->map(function ($relation) use ($organization) {
            $personEmails = $relation->person?->contactPoints
                ?->filter(fn ($cp) => $cp->contactType?->category === 'email')
                ->map(fn ($cp) => [
                    'value' => $cp->value,
                    'label' => $cp->value . ' — Persona',
                ])
                ->values()
                ->all() ?? [];

            $relationEmails = $relation->contactPoints
                ?->filter(fn ($cp) => $cp->contactType?->category === 'email')
                ->map(fn ($cp) => [
                    'value' => $cp->value,
                    'label' => $cp->value . ' — Relazione',
                ])
                ->values()
                ->all() ?? [];

            return [
                'organization_id' => $organization->id,
                'person_id' => $relation->person?->id,
                'name' => trim(($relation->person?->first_name ?? '') . ' ' . ($relation->person?->last_name ?? '')),
                'qualification' => $relation->qualification?->name,
                'department' => $relation->department?->name,
                'emails' => collect($relationEmails)
                    ->merge($personEmails)
                    ->unique('value')
                    ->values()
                    ->all(),
            ];
        });
    })->filter(fn ($item) => $item['person_id'])->values();
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-body">

        <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label">Organizzazione</label>

                <input
                    type="hidden"
                    id="organization_id"
                    name="organization_id"
                    value="{{ old('organization_id') }}"
                >

                <div class="position-relative">
                    <input
                        type="text"
                        id="organization_search"
                        class="form-control"
                        placeholder="Cerca organizzazione..."
                        autocomplete="off"
                        value=""
                        required
                    >

                    <div
                        id="organization_suggestions"
                        class="list-group position-absolute w-100 shadow-sm d-none"
                        style="z-index: 1050; max-height: 240px; overflow-y: auto;"
                    ></div>
                </div>

                <div class="form-text">
                    Digita almeno 2 caratteri per cercare.
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Persona CRM</label>

                <select id="person_id" name="person_id" class="form-select" required disabled>
                    <option value="">Prima seleziona un’organizzazione...</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Email accesso WN+</label>

                <select id="email" name="email" class="form-select" required disabled>
                    <option value="">Prima seleziona una persona...</option>
                </select>

                <div class="form-text">
                    Sono mostrate le email della persona e della relazione con l’organizzazione.
                </div>
            </div>




            <div class="col-md-3">
                <label class="form-label">Ruolo</label>

                <select name="wn_plus_role_id" class="form-select" required>
                    @foreach($roles as $role)
                        <option
                            value="{{ $role->id }}"
                            @selected(old('wn_plus_role_id', $roles->firstWhere('code', 'manager')?->id) == $role->id)
                        >
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Livello</label>

                <select name="wn_plus_level_id" class="form-select" required>
                    @foreach($levels as $level)
                        <option
                            value="{{ $level->id }}"
                            @selected(old('wn_plus_level_id', $levels->firstWhere('code', 'base')?->id) == $level->id)
                        >
                            {{ $level->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Numero massimo utenti</label>

                <input
                    type="number"
                    name="max_users"
                    class="form-control"
                    value="{{ old('max_users', 8) }}"
                    min="0"
                >
            </div>

        </div>

    </div>

    <div class="card-footer bg-white">
        <div class="d-flex justify-content-end gap-2">

            <a href="{{ route('wn-plus.accounts.index') }}" class="btn btn-outline-secondary">
                Annulla
            </a>

            <button type="submit" class="btn btn-primary">
                Salva referente
            </button>

        </div>
    </div>
</div>

<script>
    window.wnPlusOrganizations = @json($organizationsForJs);
    window.wnPlusRelations = @json($relationsForJs);

    document.addEventListener('DOMContentLoaded', function () {
        const organizationIdInput = document.getElementById('organization_id');
        const organizationSearch = document.getElementById('organization_search');
        const organizationSuggestions = document.getElementById('organization_suggestions');
        const personSelect = document.getElementById('person_id');
        const emailSelect = document.getElementById('email');

        const oldOrganizationId = @json(old('organization_id'));
        const oldPersonId = @json(old('person_id'));
        const oldEmail = @json(old('email'));

        function resetPeople() {
            personSelect.disabled = true;
            personSelect.innerHTML = '<option value="">Prima seleziona un’organizzazione...</option>';

            resetEmails();
        }

        function resetEmails() {
            emailSelect.disabled = true;
            emailSelect.innerHTML = '<option value="">Prima seleziona una persona...</option>';
        }

        function renderOrganizationSuggestions(term) {
            organizationSuggestions.innerHTML = '';

            if (!term || term.length < 2) {
                organizationSuggestions.classList.add('d-none');
                return;
            }

            const matches = window.wnPlusOrganizations.filter(function (organization) {
                return organization.name.toLowerCase().includes(term.toLowerCase());
            }).slice(0, 20);

            if (!matches.length) {
                organizationSuggestions.classList.add('d-none');
                return;
            }

            matches.forEach(function (organization) {
                const button = document.createElement('button');

                button.type = 'button';
                button.className = 'list-group-item list-group-item-action';
                button.textContent = organization.name;

                button.addEventListener('click', function () {
                    selectOrganization(organization);
                });

                organizationSuggestions.appendChild(button);
            });

            organizationSuggestions.classList.remove('d-none');
        }

        function selectOrganization(organization) {
            organizationIdInput.value = organization.id;
            organizationSearch.value = organization.name;
            organizationSuggestions.classList.add('d-none');

            refreshPeople();
        }

        function refreshPeople() {
            const organizationId = organizationIdInput.value;

            personSelect.innerHTML = '';

            if (!organizationId) {
                resetPeople();
                return;
            }

            const people = window.wnPlusRelations.filter(function (item) {
                return String(item.organization_id) === String(organizationId);
            });

            if (!people.length) {
                personSelect.disabled = true;
                personSelect.innerHTML = '<option value="">Nessuna persona collegata</option>';
                resetEmails();
                return;
            }

            personSelect.disabled = false;
            personSelect.innerHTML = '<option value="">Seleziona persona...</option>';

            people.forEach(function (item) {
                const option = document.createElement('option');

                let label = item.name;
                const details = [item.qualification, item.department].filter(Boolean).join(' · ');

                if (details) {
                    label += ' — ' + details;
                }

                option.value = item.person_id;
                option.textContent = label;

                if (oldPersonId && String(oldPersonId) === String(item.person_id)) {
                    option.selected = true;
                }

                personSelect.appendChild(option);
            });

            refreshEmails();
        }

        function refreshEmails() {
            const organizationId = organizationIdInput.value;
            const personId = personSelect.value;

            emailSelect.innerHTML = '';

            if (!organizationId || !personId) {
                resetEmails();
                return;
            }

            const relation = window.wnPlusRelations.find(function (item) {
                return String(item.organization_id) === String(organizationId)
                    && String(item.person_id) === String(personId);
            });

            const emails = relation?.emails || [];

            if (!emails.length) {
                emailSelect.disabled = true;
                emailSelect.innerHTML = '<option value="">Nessuna email disponibile</option>';
                return;
            }

            emailSelect.disabled = false;
            emailSelect.innerHTML = '<option value="">Seleziona email...</option>';

            emails.forEach(function (email) {
                const option = document.createElement('option');

                option.value = email.value;
                option.textContent = email.label;

                if (oldEmail && String(oldEmail) === String(email.value)) {
                    option.selected = true;
                }

                emailSelect.appendChild(option);
            });
        }

        organizationSearch.addEventListener('input', function () {
            organizationIdInput.value = '';
            resetPeople();
            renderOrganizationSuggestions(this.value);
        });

        organizationSearch.addEventListener('focus', function () {
            renderOrganizationSuggestions(this.value);
        });

        document.addEventListener('click', function (event) {
            if (
                !organizationSearch.contains(event.target)
                && !organizationSuggestions.contains(event.target)
            ) {
                organizationSuggestions.classList.add('d-none');
            }
        });

        personSelect.addEventListener('change', refreshEmails);

        if (oldOrganizationId) {
            const oldOrganization = window.wnPlusOrganizations.find(function (organization) {
                return String(organization.id) === String(oldOrganizationId);
            });

            if (oldOrganization) {
                selectOrganization(oldOrganization);
            }
        }
    });
</script>