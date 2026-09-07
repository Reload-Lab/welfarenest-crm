@php
    // $formId, $action, $method, $row (null in creazione), $config, $submitLabel
    $row = $row ?? null;
@endphp

<form id="{{ $formId }}" action="{{ $action }}" method="POST">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="modal-body">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label for="{{ $formId }}_code" class="form-label fw-semibold">Codice</label>
                <input
                    type="text"
                    name="code"
                    id="{{ $formId }}_code"
                    class="form-control"
                    value="{{ old('code', $row->code ?? '') }}"
                    placeholder="es. bank"
                    required
                >
                <div class="form-text">Identificativo tecnico stabile, non tradotto in UI.</div>
            </div>

            <div class="col-12 col-md-6">
                <label for="{{ $formId }}_name" class="form-label fw-semibold">Nome</label>
                <input
                    type="text"
                    name="name"
                    id="{{ $formId }}_name"
                    class="form-control"
                    value="{{ old('name', $row->name ?? '') }}"
                    placeholder="es. Banca"
                    required
                >
            </div>

            <div class="col-12">
                <label for="{{ $formId }}_description" class="form-label fw-semibold">Descrizione</label>
                <input
                    type="text"
                    name="description"
                    id="{{ $formId }}_description"
                    class="form-control"
                    value="{{ old('description', $row->description ?? '') }}"
                >
            </div>

            @foreach($config['extra_fields'] ?? [] as $field => $fieldConfig)
                <div class="col-12 col-md-6">
                    <label for="{{ $formId }}_{{ $field }}" class="form-label fw-semibold">
                        {{ $fieldConfig['label'] }}
                    </label>

                    @if(($fieldConfig['type'] ?? null) === 'select')
                        <select
                            name="{{ $field }}"
                            id="{{ $formId }}_{{ $field }}"
                            class="form-select"
                            @if($fieldConfig['required'] ?? false) required @endif
                        >
                            <option value="">Seleziona...</option>
                            @foreach($fieldConfig['options'] as $value => $optionLabel)
                                <option
                                    value="{{ $value }}"
                                    {{ (string) old($field, $row->{$field} ?? '') === (string) $value ? 'selected' : '' }}
                                >
                                    {{ $optionLabel }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input
                            type="text"
                            name="{{ $field }}"
                            id="{{ $formId }}_{{ $field }}"
                            class="form-control"
                            value="{{ old($field, $row->{$field} ?? '') }}"
                            @if($fieldConfig['required'] ?? false) required @endif
                        >
                    @endif
                </div>
            @endforeach

            <div class="col-12 col-md-4">
                <label for="{{ $formId }}_sort_order" class="form-label fw-semibold">Ordinamento</label>
                <input
                    type="number"
                    name="sort_order"
                    id="{{ $formId }}_sort_order"
                    class="form-control"
                    value="{{ old('sort_order', $row->sort_order ?? 0) }}"
                >
            </div>

            <div class="col-12 col-md-8 d-flex align-items-end">
                <div class="form-check form-switch mb-2">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        name="is_active"
                        id="{{ $formId }}_is_active"
                        value="1"
                        {{ old('is_active', $row->is_active ?? true) ? 'checked' : '' }}
                    >
                    <label class="form-check-label fw-semibold" for="{{ $formId }}_is_active">
                        Attivo
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annulla</button>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
