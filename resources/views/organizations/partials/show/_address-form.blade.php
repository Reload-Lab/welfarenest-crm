<div class="mb-3">
    <label class="form-label">Tipo</label>
    <select name="address_type_id" class="form-select" required>
        @foreach($addressTypes as $type)
            <option value="{{ $type->id }}"
                @selected(old('address_type_id', $address?->address_type_id) == $type->id)>
                {{ $type->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Etichetta</label>
    <input type="text" name="label" class="form-control"
           value="{{ old('label', $address?->label) }}">
</div>

<div class="row">
    <div class="col-8 mb-3">
        <label class="form-label">Via/Piazza</label>
        <input type="text" name="street" class="form-control"
               value="{{ old('street', $address?->street) }}">
    </div>
    <div class="col-4 mb-3">
        <label class="form-label">N°</label>
        <input type="text" name="street_number" class="form-control"
               value="{{ old('street_number', $address?->street_number) }}">
    </div>
</div>

<div class="row">
    <div class="col-8 mb-3">
        <label class="form-label">Città</label>
        <input type="text"
            name="city"
            class="form-control js-address-city"
            value="{{ old('city', $address?->city) }}"
            list="municipalities-list">

        <datalist id="municipalities-list"></datalist>
    </div>
    <div class="col-4 mb-3">
        <label class="form-label">CAP</label>
        <input type="text" name="postal_code" class="form-control"
               value="{{ old('postal_code', $address?->postal_code) }}">
    </div>
</div>

<div class="row">
    <div class="col-6 mb-3">
        <label class="form-label">Provincia</label>
        <input type="text"
            name="province"
            class="form-control js-address-province"
            value="{{ old('province', $address?->province) }}">
    </div>
    <div class="col-6 mb-3">
        <label class="form-label">Regione</label>
        <input type="text"
            name="region"
            class="form-control js-address-region"
            value="{{ old('region', $address?->region) }}">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Paese</label>
    <select name="country" class="form-select js-address-country">
        <option value="Italia" @selected(old('country', $address?->country ?? 'Italia') === 'Italia')>
            Italia
        </option>
        <option value="Estero" @selected(old('country', $address?->country) === 'Estero')>
            Estero
        </option>
    </select>
</div>

<div class="form-check">
    <input class="form-check-input" type="checkbox" name="is_primary" value="1"
        @checked(old('is_primary', $address?->is_primary))>
    <label class="form-check-label">
        Imposta come primario
    </label>
</div>