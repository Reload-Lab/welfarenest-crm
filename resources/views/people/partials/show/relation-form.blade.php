@php
    $relationModel = (isset($relation) && data_get($relation, 'id')) ? $relation : null;
    $isEditing = isset($relation) && isset($relation->id);
    $relationContext = $relationContext ?? 'person';
    $personModel = $person ?? null;
    $organizationModel = $selectedOrganization ?? ($organization ?? null);
    $selectedPerson = $selectedPerson ?? $personModel;
    $selectedOrganizationId = old('organization_id', $relationModel->organization_id ?? $organizationModel?->id ?? '');
    $selectedPersonId = old('person_id', $relationModel->person_id ?? $selectedPerson?->id ?? '');
    $cancelUrl = $relationContext === 'organization'
        ? ($organizationModel ? route('organizations.show', $organizationModel) : null)
        : ($personModel ? route('people.show', $personModel) : null);

    if ($relationContext === 'organization') {
        if ($isEditing) {
            $formAction = $relationModel
                ? route('organizations.relations.update', [$organizationModel, $relationModel])
                : '#';
        } else {
            $formAction = $organizationModel
                ? route('organizations.relations.store', $organizationModel)
                : '#';
        }
    } elseif ($isEditing) {
        $formAction = $personModel
            ? route('people.relations.update', [$personModel, $relationModel])
            : '#';
    } else {
        $formAction = $personModel
            ? route('people.relations.store', $personModel)
            : '#';
    }

@endphp

<form method="POST" action="{{ $formAction }}">
    @csrf
    @if($isEditing)
        @method('PUT')
    @endif

    @if($relationContext === 'organization')
        <input type="hidden" name="organization_id" value="{{ $organizationModel?->id }}">
        <input type="hidden" name="return_to" value="organization">
    @endif

    @if($isEditing)
        <input type="hidden" name="relation_id" value="{{ $relationModel->id }}">
    @endif

    <div class="row g-3">

@if($relationContext === 'organization')
    <div class="col-12 col-lg-6">
        <label class="form-label fw-semibold">Persona</label>

        @if($isEditing)
            <input type="hidden" name="person_id" value="{{ $relationModel->person_id }}">

            <input
                type="text"
                class="form-control"
                value="{{ $relationModel->person?->full_name ?: ('#' . $relationModel->person_id) }}"
                readonly
            >
        @else
            <select
                name="person_id"
                id="person_id"
                class="form-select js-person-search @error('person_id') is-invalid @enderror"
                data-search-url="{{ route('people.search') }}"
                data-placeholder="Cerca persona..."
                data-selected-id="{{ $selectedPersonId }}"
                data-selected-label="{{ $selectedPerson?->full_name ?: ($selectedPersonId ? ('#' . $selectedPersonId) : '') }}"
            >
                <option value="">Seleziona...</option>
                @if($selectedPersonId)
                    <option value="{{ $selectedPersonId }}" selected>
                        {{ $selectedPerson?->full_name ?: ('#' . $selectedPersonId) }}
                    </option>
                @endif
            </select>

            @error('person_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        @endif
    </div>
@endif

@if($relationContext === 'person')
    <div class="col-12 col-lg-6">
        <label class="form-label fw-semibold">Organizzazione</label>

        @if($isEditing)
            <input type="hidden" name="organization_id" value="{{ $relationModel->organization_id }}">

            <input
                type="text"
                class="form-control"
                value="{{ $relationModel->organization?->name ?: $relationModel->organization?->legal_name ?: ('#' . $relationModel->organization_id) }}"
                readonly
            >
        @else
            <select
                name="organization_id"
                id="organization_id"
                class="form-select js-organization-search @error('organization_id') is-invalid @enderror"
                data-search-url="{{ route('organizations.search') }}"
                data-placeholder="Cerca organizzazione..."
                data-selected-id="{{ $selectedOrganizationId }}"
                data-selected-label="{{ $organizationModel?->name ?: $organizationModel?->legal_name ?: ($selectedOrganizationId ? ('#' . $selectedOrganizationId) : '') }}"
            >
                <option value="">Seleziona...</option>
                @if($selectedOrganizationId)
                    <option value="{{ $selectedOrganizationId }}" selected>
                        {{ $organizationModel?->name ?: $organizationModel?->legal_name ?: ('#' . $selectedOrganizationId) }}
                    </option>
                @endif
            </select>

            @error('organization_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        @endif
    </div>
@endif
        <div class="col-12 col-md-6 col-lg-6">
            <label for="is_active" class="form-label fw-semibold">Stato</label>
            <select
                name="is_active"
                id="is_active"
                class="form-select @error('is_active') is-invalid @enderror"
            >
                <option value="1" {{ (int) old('is_active', $relation?->is_active ?? 1) === 1 ? 'selected' : '' }}>
                    Attiva
                </option>
                <option value="0" {{ (int) old('is_active', $relation?->is_active ?? 1) === 0 ? 'selected' : '' }}>
                    Non attiva
                </option>
            </select>
            @error('is_active')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-6 col-lg-6">
            <label for="department_id" class="form-label fw-semibold">Dipartimento</label>
            <select
                name="department_id"
                id="department_id"
                class="form-select @error('department_id') is-invalid @enderror"
            >
                <option value="">Nessuno</option>
                    @foreach($departments as $department)
                    <option
                        value="{{ $department->id }}"
                        {{ (string) old('department_id', $relation?->department_id) === (string) $department->id ? 'selected' : '' }}
                    >
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
            @error('department_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-6 col-lg-6">
            <label for="qualification_id" class="form-label fw-semibold">Qualifica</label>
            <select
                name="qualification_id"
                id="qualification_id"
                class="form-select @error('qualification_id') is-invalid @enderror"
            >
                <option value="">Nessuna</option>
                    @foreach($qualifications as $qualification)
                    <option
                        value="{{ $qualification->id }}"
                        {{ (string) old('qualification_id', $relation?->qualification_id) === (string) $qualification->id ? 'selected' : '' }}
                    >
                        {{ $qualification->name }}
                    </option>
                @endforeach
            </select>
            @error('qualification_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        
        {{--
        <div class="col-12 col-md-6 col-lg-4 d-flex align-items-end">
            <div class="form-check form-switch mb-2">
                <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    name="is_primary"
                    id="is_primary"
                    value="1"
                    {{ old('is_primary', $relation?->is_primary) ? 'checked' : '' }}
                >
                <label class="form-check-label fw-semibold" for="is_primary">
                    Relazione principale
                </label>
            </div>
        </div>
        --}}

        {{-- Date per ora non disponibili --}}
        {{--
        <div class="col-12 col-md-6 col-lg-6">
            <label for="start_date" class="form-label fw-semibold">Data inizio</label>
            <input
                type="date"
                name="start_date"
                id="start_date"
                class="form-control @error('start_date') is-invalid @enderror"
                value="{{ old('start_date', optional($relation?->start_date)->format('Y-m-d')) }}"
            >
            @error('start_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-6 col-lg-6">
            <label for="end_date" class="form-label fw-semibold">Data fine</label>
            <input
                type="date"
                name="end_date"
                id="end_date"
                class="form-control @error('end_date') is-invalid @enderror"
                value="{{ old('end_date', optional($relation?->end_date)->format('Y-m-d')) }}"
            >
            @error('end_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        --}}



    </div>

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mt-4">
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                Chiudi
            </button>

            @if($cancelUrl)
                <a href="{{ $cancelUrl }}" class="btn btn-outline-secondary">
                    {{ $isEditing ? 'Annulla modifica' : 'Annulla' }}
                </a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary">
            {{ $isEditing ? 'Salva relazione' : 'Aggiungi relazione' }}
        </button>
    </div>
</form>
