@extends('layouts.app')

@section('content')

<h2 class="h4 mb-4">Nuova organizzazione</h2>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('organizations.store') }}">
    @csrf

    <div class="card">
        <div class="card-body">

            <h3 class="h6 mb-3">Dati anagrafici</h3>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nome</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="legal_name" class="form-label">Ragione sociale</label>
                    <input
                        type="text"
                        id="legal_name"
                        name="legal_name"
                        class="form-control @error('legal_name') is-invalid @enderror"
                        value="{{ old('legal_name') }}"
                    >
                    @error('legal_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="organization_type_id" class="form-label">ID tipo organizzazione</label>
                    <input
                        type="number"
                        id="organization_type_id"
                        name="organization_type_id"
                        class="form-control @error('organization_type_id') is-invalid @enderror"
                        value="{{ old('organization_type_id') }}"
                        min="1"
                    >
                    @error('organization_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        Temporaneo: nel prossimo step lo trasformiamo in select.
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <h3 class="h6 mb-3">Dati amministrativi</h3>

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="vat_number" class="form-label">Partita IVA</label>
                    <input
                        type="text"
                        id="vat_number"
                        name="vat_number"
                        class="form-control @error('vat_number') is-invalid @enderror"
                        value="{{ old('vat_number') }}"
                    >
                    @error('vat_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="tax_code" class="form-label">Codice fiscale</label>
                    <input
                        type="text"
                        id="tax_code"
                        name="tax_code"
                        class="form-control @error('tax_code') is-invalid @enderror"
                        value="{{ old('tax_code') }}"
                    >
                    @error('tax_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="sdi_code" class="form-label">Codice SDI</label>
                    <input
                        type="text"
                        id="sdi_code"
                        name="sdi_code"
                        class="form-control @error('sdi_code') is-invalid @enderror"
                        value="{{ old('sdi_code') }}"
                    >
                    @error('sdi_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <h3 class="h6 mb-3">Stato</h3>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="form-check">
                        <input
                            type="checkbox"
                            id="is_split_payment"
                            name="is_split_payment"
                            value="1"
                            class="form-check-input"
                            {{ old('is_split_payment') ? 'checked' : '' }}
                        >
                        <label for="is_split_payment" class="form-check-label">
                            Split payment
                        </label>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-check">
                        <input
                            type="checkbox"
                            id="is_active"
                            name="is_active"
                            value="1"
                            class="form-check-input"
                            {{ old('is_active', 1) ? 'checked' : '' }}
                        >
                        <label for="is_active" class="form-check-label">
                            Organizzazione attiva
                        </label>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-success">Salva</button>
        <a href="{{ route('organizations.index') }}" class="btn btn-secondary">Annulla</a>
    </div>
</form>

@endsection