@php
    $isEditing = isset($relation) && $relation;
    $formAction = $isEditing
        ? route('people.relations.update', [$person, $relation])
        : route('people.relations.store', $person);
@endphp

<form method="POST" action="{{ $formAction }}">
    @csrf
    @if($isEditing)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <label for="organization_id" class="form-label fw-semibold">Organizzazione</label>
            <select
                name="organization_id"
                id="organization_id"
                class="form-select @error('organization_id') is-invalid @enderror"
            >
                <option value="">Seleziona...</option>
                @foreach($organizations as $organization)
                    @php
                        $organizationLabel = $organization->name ?: $organization->legal_name;
                    @endphp
                    <option
                        value="{{ $organization->id }}"
                        {{ (string) old('organization_id', $relation->organization_id ?? '') === (string) $organization->id ? 'selected' : '' }}
                    >
                        {{ $organizationLabel }}
                    </option>
                @endforeach
            </select>
            @error('organization_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-6 col-lg-3">
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
                        {{ (string) old('qualification_id', $relation->qualification_id ?? '') === (string) $qualification->id ? 'selected' : '' }}
                    >
                        {{ $qualification->name }}
                    </option>
                @endforeach
            </select>
            @error('qualification_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-6 col-lg-3">
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
                        {{ (string) old('department_id', $relation->department_id ?? '') === (string) $department->id ? 'selected' : '' }}
                    >
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
            @error('department_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <label for="start_date" class="form-label fw-semibold">Data inizio</label>
            <input
                type="date"
                name="start_date"
                id="start_date"
                class="form-control @error('start_date') is-invalid @enderror"
                value="{{ old('start_date', optional($relation->start_date ?? null)->format('Y-m-d')) }}"
            >
            @error('start_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <label for="end_date" class="form-label fw-semibold">Data fine</label>
            <input
                type="date"
                name="end_date"
                id="end_date"
                class="form-control @error('end_date') is-invalid @enderror"
                value="{{ old('end_date', optional($relation->end_date ?? null)->format('Y-m-d')) }}"
            >
            @error('end_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <label for="is_active" class="form-label fw-semibold">Stato</label>
            <select
                name="is_active"
                id="is_active"
                class="form-select @error('is_active') is-invalid @enderror"
            >
                <option value="1" {{ old('is_active', isset($relation) ? (int) $relation->is_active : 1) === 1 ? 'selected' : '' }}>
                    Attiva
                </option>
                <option value="0" {{ old('is_active', isset($relation) ? (int) $relation->is_active : 1) === 0 ? 'selected' : '' }}>
                    Non attiva
                </option>
            </select>
            @error('is_active')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-6 col-lg-3 d-flex align-items-end">
            <div class="form-check form-switch mb-2">
                <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    name="is_primary"
                    id="is_primary"
                    value="1"
                    {{ old('is_primary', $relation->is_primary ?? false) ? 'checked' : '' }}
                >
                <label class="form-check-label fw-semibold" for="is_primary">
                    Relazione principale
                </label>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mt-4">
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                Chiudi
            </button>

            @if($isEditing)
                <a href="{{ route('people.show', $person) }}" class="btn btn-outline-secondary">
                    Annulla modifica
                </a>
            @endif
        </div>

        <button type="submit" class="btn btn-primary">
            {{ $isEditing ? 'Salva relazione' : 'Aggiungi relazione' }}
        </button>
    </div>
</form>
