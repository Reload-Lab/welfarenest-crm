@php
    $lead = $lead ?? null;
    $selectedStatusId = old('lead_status_id', $lead->lead_status_id ?? ($defaultStatusId ?? null));
    $selectedSourceId = old('lead_source_id', $lead->lead_source_id ?? null);
    $selectedUserId = old('assigned_user_id', $lead->assigned_user_id ?? null);
    $expectedCloseDate = old('expected_close_date', $lead?->expected_close_date?->format('Y-m-d'));
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">

        <div class="row g-4">
            <div class="col-12">
                <label for="name" class="form-label fw-semibold">Titolo</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $lead->name ?? '') }}"
                    placeholder="Es. Richiesta informazioni welfare aziendale"
                    required
                >
                <div class="form-text">Come riconoscere questo contatto nell'elenco. Non è il nome della persona.</div>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <hr class="my-4">

        <h3 class="h6 fw-semibold mb-3">Contatto</h3>

        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <label for="first_name" class="form-label fw-semibold">Nome</label>
                <input
                    type="text"
                    name="first_name"
                    id="first_name"
                    class="form-control @error('first_name') is-invalid @enderror"
                    value="{{ old('first_name', $lead->first_name ?? '') }}"
                    placeholder="Es. Mario"
                >
                @error('first_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-lg-4">
                <label for="last_name" class="form-label fw-semibold">Cognome</label>
                <input
                    type="text"
                    name="last_name"
                    id="last_name"
                    class="form-control @error('last_name') is-invalid @enderror"
                    value="{{ old('last_name', $lead->last_name ?? '') }}"
                    placeholder="Es. Rossi"
                >
                @error('last_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-lg-4">
                <label for="company_name" class="form-label fw-semibold">Azienda dichiarata</label>
                <input
                    type="text"
                    name="company_name"
                    id="company_name"
                    class="form-control @error('company_name') is-invalid @enderror"
                    value="{{ old('company_name', $lead->company_name ?? '') }}"
                    placeholder="Es. Rossi Spa"
                >
                <div class="form-text">Testo libero: diventa un'organizzazione solo alla conversione.</div>
                @error('company_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <hr class="my-4">

        <h3 class="h6 fw-semibold mb-3">Gestione</h3>

        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <label for="lead_status_id" class="form-label fw-semibold">Stato</label>
                <select
                    name="lead_status_id"
                    id="lead_status_id"
                    class="form-select @error('lead_status_id') is-invalid @enderror"
                    required
                >
                    @foreach($statuses as $status)
                        <option
                            value="{{ $status->id }}"
                            {{ (string) $selectedStatusId === (string) $status->id ? 'selected' : '' }}
                        >
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
                @error('lead_status_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-lg-4">
                <label for="lead_source_id" class="form-label fw-semibold">Fonte</label>
                <select
                    name="lead_source_id"
                    id="lead_source_id"
                    class="form-select @error('lead_source_id') is-invalid @enderror"
                >
                    <option value="">Non specificata</option>
                    @foreach($sources as $source)
                        <option
                            value="{{ $source->id }}"
                            {{ (string) $selectedSourceId === (string) $source->id ? 'selected' : '' }}
                        >
                            {{ $source->name }}
                        </option>
                    @endforeach
                </select>
                @error('lead_source_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-lg-4">
                <label for="assigned_user_id" class="form-label fw-semibold">Assegnato a</label>
                <select
                    name="assigned_user_id"
                    id="assigned_user_id"
                    class="form-select @error('assigned_user_id') is-invalid @enderror"
                >
                    <option value="">Nessuno</option>
                    @foreach($users as $user)
                        <option
                            value="{{ $user->id }}"
                            {{ (string) $selectedUserId === (string) $user->id ? 'selected' : '' }}
                        >
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                @error('assigned_user_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-lg-4">
                <label for="estimated_value" class="form-label fw-semibold">Valore stimato</label>
                <div class="input-group">
                    <span class="input-group-text">&euro;</span>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="estimated_value"
                        id="estimated_value"
                        class="form-control @error('estimated_value') is-invalid @enderror"
                        value="{{ old('estimated_value', $lead->estimated_value ?? '') }}"
                    >
                    @error('estimated_value')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <label for="expected_close_date" class="form-label fw-semibold">Chiusura prevista</label>
                <input
                    type="date"
                    name="expected_close_date"
                    id="expected_close_date"
                    class="form-control @error('expected_close_date') is-invalid @enderror"
                    value="{{ $expectedCloseDate }}"
                >
                @error('expected_close_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-lg-4">
                <label for="lost_reason" class="form-label fw-semibold">Motivo della perdita</label>
                <input
                    type="text"
                    name="lost_reason"
                    id="lost_reason"
                    class="form-control @error('lost_reason') is-invalid @enderror"
                    value="{{ old('lost_reason', $lead->lost_reason ?? '') }}"
                >
                <div class="form-text">Viene conservato solo se lo stato scelto è di perdita.</div>
                @error('lost_reason')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="description" class="form-label fw-semibold">Descrizione</label>
                <textarea
                    name="description"
                    id="description"
                    rows="4"
                    class="form-control @error('description') is-invalid @enderror"
                    placeholder="Cosa ha chiesto, come è arrivato, cosa è stato detto finora."
                >{{ old('description', $lead->description ?? '') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <div class="form-check form-switch">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        name="is_active"
                        id="is_active"
                        value="1"
                        {{ old('is_active', $lead->is_active ?? true) ? 'checked' : '' }}
                    >
                    <label class="form-check-label fw-semibold" for="is_active">Attivo</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer bg-white border-0 px-4 py-3">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">
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
