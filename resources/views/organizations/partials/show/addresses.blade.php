<div class="card h-100">
    {{-- HEADER --}}
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Indirizzi</h5>

        <x-crm.icon-button
            icon="add"
            icon-group="actions"
            title="Nuovo recapito"
            data-bs-toggle="modal"
            data-bs-target="#modalCreateAddress"
        />
       
    </div>

    {{-- BODY --}}
    <div class="card-body p-0">

        @if($organization->addresses->isEmpty())
            <div class="p-3 text-muted small">
                Nessun indirizzo presente
            </div>
        @else

            <ul class="list-group list-group-flush">

                @foreach($organization->addresses as $address)

                    <li class="list-group-item crm-address-item d-flex justify-content-between align-items-start gap-3">

                        {{-- LEFT --}}
                        <div class="crm-address-content">

                            {{-- TAG + PRIMARY --}}
                            <div class="mb-1 d-flex align-items-center gap-2 flex-wrap">

                                <x-crm.tag
                                    :label="$address->addressType?->name"
                                    icon-group="entities"
                                    icon-name="address"
                                />

                                @if($address->is_primary)
                                    <x-crm.status label="Primario" />
                                @endif

                            </div>

                            {{-- ADDRESS TEXT --}}
                            <div class="small">

                                @if($address->label)
                                    <div class="fw-semibold">
                                        {{ $address->label }}
                                    </div>
                                @endif

                                <div>
                                    {{ $address->street }} {{ $address->street_number }}
                                </div>

                                <div>
                                    {{ $address->postal_code }} {{ $address->city }}
                                    @if($address->province)
                                        ({{ $address->province }})
                                    @endif
                                </div>

                                @if($address->region || $address->country)
                                @php
                                    $mapsQuery = urlencode(
                                        trim(
                                            ($address->street ?? '') . ' ' .
                                            ($address->street_number ?? '') . ', ' .
                                            ($address->postal_code ?? '') . ' ' .
                                            ($address->city ?? '')
                                        )
                                    );

                                    $fullAddress = trim(
                                        ($address->street ?? '') . ' ' .
                                        ($address->street_number ?? '') . "\n" .
                                        ($address->postal_code ?? '') . ' ' .
                                        ($address->city ?? '') . ' (' . ($address->province ?? '') . ')' . "\n" .
                                        ($address->region ?? '') . ' ' .
                                        ($address->country ?? '')
                                    );
                                @endphp

                                @endif
                            </div>

                                <div class="crm-address-actions">

                                    <a
                                        href="https://www.google.com/maps/search/?api=1&query={{ $mapsQuery }}"
                                        target="_blank"
                                        class="crm-address-action"
                                    >
                                        <x-icon group="actions" name="map" />
                                        <span>Mappa</span>
                                    </a>

                                    <button
                                        type="button"
                                        class="crm-address-action"
                                        data-copy-text="{{ $fullAddress }}"
                                        onclick="copyCrmText(this)"
                                    >
                                        <x-icon group="actions" name="copy" />
                                        <span class="crm-copy-label">Copia</span>
                                    </button>

                                </div>



                        </div>





@include('components.crm.row-actions', [
    'edit' => route('organizations.edit', $organization),
    'delete' => route('organizations.destroy', $organization),
    'deleteConfirm' => 'Confermi l\'eliminazione di questa organizzazione?',
])

                    </li>

                    {{-- MODAL EDIT --}}
                    <div class="modal fade" id="addressEditModal-{{ $address->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST"
                                  action="{{ route('organizations.addresses.update', [$organization, $address]) }}"
                                  class="modal-content">

                                @csrf
                                @method('PUT')

                                <div class="modal-header">
                                    <h5 class="modal-title">Modifica indirizzo</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">

                                    @include('organizations.partials.show._address-form', [
                                        'address' => $address,
                                        'addressTypes' => $addressTypes
                                    ])

                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Salva</button>
                                </div>

                            </form>
                        </div>
                    </div>

                @endforeach

            </ul>

        @endif

    </div>
</div>


{{-- MODAL CREATE --}}
<div class="modal fade" id="modalCreateAddress" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST"
              action="{{ route('organizations.addresses.store', $organization) }}"
              class="modal-content">

            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Nuovo indirizzo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                @include('organizations.partials.show._address-form', [
                    'address' => null,
                    'addressTypes' => $addressTypes
                ])

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Salva</button>
            </div>

        </form>
    </div>
</div>

<script>

function updateMunicipalitySuggestions(searchValue, datalist, municipalities) {
    datalist.innerHTML = '';

    const term = searchValue.trim().toLowerCase();

    if (term.length < 2) {
        return;
    }

    const results = municipalities
        .filter(item => item.city.toLowerCase().startsWith(term))
        .sort((a, b) => b.population - a.population)
        .slice(0, 12);

    results.forEach(item => {
        const option = document.createElement('option');
        option.value = item.city;
        option.label = `${item.city} (${item.province}) - ${item.region}`;
        datalist.appendChild(option);
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    
    const response = await fetch('/data/comuni.json');
    const rawMunicipalities = await response.json();
    const municipalities = rawMunicipalities.map(item => ({
        city: item.nome,
        province: item.sigla,
        region: item.regione?.nome ?? '',
        postalCodes: item.cap ?? [],
        population: item.popolazione ?? 0,
    }));

    const datalist = document.getElementById('municipalities-list');


    document.querySelectorAll('.modal').forEach(modal => {

        const countryField = modal.querySelector('.js-address-country');
        const cityField = modal.querySelector('.js-address-city');
        const provinceField = modal.querySelector('.js-address-province');
        const regionField = modal.querySelector('.js-address-region');
        const postalCodeField = modal.querySelector('[name="postal_code"]');

        if (!countryField || !cityField || !provinceField || !regionField) {
            return;
        }

        // SUGGESTIONS
        cityField.addEventListener('input', () => {

            if (countryField.value !== 'Italia') {
                return;
            }

            updateMunicipalitySuggestions(
                cityField.value,
                datalist,
                municipalities
            );
        });

        // AUTO-COMPILAZIONE
        cityField.addEventListener('change', () => {

            const selected = municipalities.find(item =>
                item.city.toLowerCase() === cityField.value.trim().toLowerCase()
            );

            if (!selected) {
                return;
            }

            provinceField.value = selected.province;
            regionField.value = selected.region;

            if (
                postalCodeField &&
                selected.postalCodes.length === 1
            ) {
                postalCodeField.value = selected.postalCodes[0];
            }

            datalist.innerHTML = '';
            cityField.blur();

        });

        // COUNTRY CHANGE
        countryField.addEventListener('change', () => {

            const isItaly = countryField.value === 'Italia';

            provinceField.readOnly = isItaly;
            regionField.readOnly = isItaly;

            if (!isItaly) {
                provinceField.value = '';
                regionField.value = '';
            }
        });

        countryField.dispatchEvent(new Event('change'));
    });
    });
</script>

<script>
function copyCrmText(button) {

    const text = button.dataset.copyText || '';

    if (!text) {
        return;
    }

    const label = button.querySelector('.crm-copy-label');

    const showCopiedState = () => {

        if (!label) {
            return;
        }

        const original = label.dataset.originalText || label.textContent;

        label.dataset.originalText = original;
        label.textContent = 'Copiato ✓';

        button.classList.add('is-copied');

        clearTimeout(button._copyTimeout);

        button._copyTimeout = setTimeout(() => {
            label.textContent = original;
            button.classList.remove('is-copied');
        }, 1500);
    };

    if (navigator.clipboard && window.isSecureContext) {

        navigator.clipboard.writeText(text)
            .then(showCopiedState);

        return;
    }

    const textarea = document.createElement('textarea');

    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';

    document.body.appendChild(textarea);

    textarea.focus();
    textarea.select();

    document.execCommand('copy');

    document.body.removeChild(textarea);

    showCopiedState();
}
</script>