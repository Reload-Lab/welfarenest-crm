@php
    $cp = $contactPoint;
    $bag = $errors->getBag($errorBag ?? 'default');
@endphp

<form action="{{ $action }}" method="POST" class="row g-3">
    @csrf

    <div class="col-md-3">
        <label for="{{ $formIdPrefix }}_contact_type_id" class="form-label">Tipo *</label>
        <select
            id="{{ $formIdPrefix }}_contact_type_id"
            name="contact_type_id"
            class="form-select @if($bag->has('contact_type_id')) is-invalid @endif"
            required
        >
            <option value="">Seleziona...</option>
            @foreach ($contactTypes as $type)
                <option
                    value="{{ $type->id }}"
                    data-category="{{ $type->category }}"
                    @selected(old('contact_type_id', $cp?->contact_type_id) == $type->id)
                >
                    {{ $type->name }}
                </option>
            @endforeach
        </select>
        @if($bag->has('contact_type_id'))
            <div class="invalid-feedback">{{ $bag->first('contact_type_id') }}</div>
        @endif
    </div>

    <div class="col-md-4">
        <label for="{{ $formIdPrefix }}_value" class="form-label">Valore *</label>
        <input
            type="text"
            id="{{ $formIdPrefix }}_value"
            name="value"
            value="{{ old('value', $cp?->value) }}"
            class="form-control @if($bag->has('value')) is-invalid @endif"
            required
            placeholder="Es. info@azienda.it / 06 123456 / https://..."
        >
        @if($bag->has('value'))
            <div class="invalid-feedback">{{ $bag->first('value') }}</div>
        @endif
    </div>

    <div class="col-md-3">
        <label for="{{ $formIdPrefix }}_contact_usage_id" class="form-label">Uso</label>
        <select
            id="{{ $formIdPrefix }}_contact_usage_id"
            name="contact_usage_id"
            class="form-select @if($bag->has('contact_usage_id')) is-invalid @endif"
        >
            <option value="">Seleziona...</option>
            @foreach ($contactUsages as $usage)
                <option
                    value="{{ $usage->id }}"
                    @selected(old('contact_usage_id', $cp?->contact_usage_id) == $usage->id)
                >
                    {{ $usage->name }}
                </option>
            @endforeach
        </select>
        @if($bag->has('contact_usage_id'))
            <div class="invalid-feedback">{{ $bag->first('contact_usage_id') }}</div>
        @endif
    </div>

    <div class="col-md-2">
        <label for="{{ $formIdPrefix }}_label" class="form-label">Etichetta</label>
        <input
            type="text"
            id="{{ $formIdPrefix }}_label"
            name="label"
            value="{{ old('label', $cp?->label) }}"
            class="form-control @if($bag->has('label')) is-invalid @endif"
            placeholder="Segreteria"
        >
        @if($bag->has('label'))
            <div class="invalid-feedback">{{ $bag->first('label') }}</div>
        @endif
    </div>

    <div class="col-12">
        <div class="d-flex flex-wrap gap-3">
            <div class="form-check">
                <input
                    class="form-check-input"
                    type="checkbox"
                    value="1"
                    id="{{ $formIdPrefix }}_is_primary"
                    name="is_primary"
                    @checked(old('is_primary', $cp?->is_primary))
                >
                <label class="form-check-label" for="{{ $formIdPrefix }}_is_primary">
                    Primario
                </label>
            </div>

            <div class="form-check">
                <input
                    class="form-check-input"
                    type="checkbox"
                    value="1"
                    id="{{ $formIdPrefix }}_is_active"
                    name="is_active"
                    @checked(old('is_active', $cp?->is_active ?? true))
                >
                <label class="form-check-label" for="{{ $formIdPrefix }}_is_active">
                    Attivo
                </label>
            </div>
        </div>
    </div>

    <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">
            <x-icon group="actions" name="save" class="me-1" />
            Salva recapito
        </button>
    </div>
</form>