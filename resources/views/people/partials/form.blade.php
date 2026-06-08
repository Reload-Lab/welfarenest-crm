@php
    $person = $person ?? null;
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <label for="first_name" class="form-label fw-semibold">Nome</label>
                <input
                    type="text"
                    name="first_name"
                    id="first_name"
                    class="form-control @error('first_name') is-invalid @enderror"
                    value="{{ old('first_name', $person->first_name ?? '') }}"
                    placeholder="Es. Mario"
                >
                @error('first_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-lg-6">
                <label for="last_name" class="form-label fw-semibold">Cognome</label>
                <input
                    type="text"
                    name="last_name"
                    id="last_name"
                    class="form-control @error('last_name') is-invalid @enderror"
                    value="{{ old('last_name', $person->last_name ?? '') }}"
                    placeholder="Es. Rossi"
                >
                @error('last_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="card-footer bg-white border-0 px-4 py-3">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <a href="{{ route('people.index') }}" class="btn btn-outline-secondary">
                Torna all'elenco
            </a>

            <div class="d-flex gap-2">
                <x-crm.button 
                    type="submit"
                    icon="save"
                >
                    {{ $submitLabel }}
                </x-crm.button>
            </div>
        </div>
    </div>
</div>
