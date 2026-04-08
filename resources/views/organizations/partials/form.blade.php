@php
    $organization = $organization ?? null;
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <label for="name" class="form-label fw-semibold">Nome</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $organization->name ?? '') }}"
                    placeholder="Es. Beta SRL"
                >
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-lg-6">
                <label for="legal_name" class="form-label fw-semibold">Ragione sociale</label>
                <input
                    type="text"
                    name="legal_name"
                    id="legal_name"
                    class="form-control @error('legal_name') is-invalid @enderror"
                    value="{{ old('legal_name', $organization->legal_name ?? '') }}"
                    placeholder="Es. Beta Produzione Laminati S.r.l."
                >
                @error('legal_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <label for="vat_number" class="form-label fw-semibold">Partita IVA</label>
                <input
                    type="text"
                    name="vat_number"
                    id="vat_number"
                    class="form-control @error('vat_number') is-invalid @enderror"
                    value="{{ old('vat_number', $organization->vat_number ?? '') }}"
                    placeholder="11 cifre"
                >
                @error('vat_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <label for="tax_code" class="form-label fw-semibold">Codice fiscale</label>
                <input
                    type="text"
                    name="tax_code"
                    id="tax_code"
                    class="form-control @error('tax_code') is-invalid @enderror"
                    value="{{ old('tax_code', $organization->tax_code ?? '') }}"
                >
                @error('tax_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <label for="sdi_code" class="form-label fw-semibold">Codice SDI</label>
                <input
                    type="text"
                    name="sdi_code"
                    id="sdi_code"
                    class="form-control @error('sdi_code') is-invalid @enderror"
                    value="{{ old('sdi_code', $organization->sdi_code ?? '') }}"
                >
                @error('sdi_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="organization_type_id" class="form-label fw-semibold">Tipo organizzazione</label>
                <select
                    name="organization_type_id"
                    id="organization_type_id"
                    class="form-select @error('organization_type_id') is-invalid @enderror"
                >
                    <option value="">Seleziona...</option>
                    @foreach($organizationTypes as $type)
                        <option
                            value="{{ $type->id }}"
                            {{ (string) old('organization_type_id', $organization->organization_type_id ?? '') === (string) $type->id ? 'selected' : '' }}
                        >
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
                @error('organization_type_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-3">
                <label for="is_active" class="form-label fw-semibold">Stato</label>
                <select
                    name="is_active"
                    id="is_active"
                    class="form-select @error('is_active') is-invalid @enderror"
                >
                    <option value="1" {{ old('is_active', isset($organization) ? (int) $organization->is_active : 1) == 1 ? 'selected' : '' }}>
                        Attivo
                    </option>
                    <option value="0" {{ old('is_active', isset($organization) ? (int) $organization->is_active : 1) == 0 ? 'selected' : '' }}>
                        Non attivo
                    </option>
                </select>
                @error('is_active')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-3 d-flex align-items-end">
                <div class="form-check form-switch mb-2">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        name="is_split_payment"
                        id="is_split_payment"
                        value="1"
                        {{ old('is_split_payment', $organization->is_split_payment ?? false) ? 'checked' : '' }}
                    >
                    <label class="form-check-label fw-semibold" for="is_split_payment">
                        Split payment
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer bg-white border-0 px-4 py-3">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <a href="{{ route('organizations.index') }}" class="btn btn-outline-secondary">
                Torna all'elenco
            </a>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    {{ $submitLabel }}
                </button>
            </div>
        </div>
    </div>
</div>