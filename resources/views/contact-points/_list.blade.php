<div class="crm-contact-point-list">
    @foreach ($contactPoints as $contactPoint)
        @php
            $type = $contactPoint->contactType;
            $usage = $contactPoint->contactUsage;
            $code = $type?->code;
            $category = $type?->category;
            $value = $contactPoint->value;

            $iconGroup = 'contact';
            $iconName = 'contact_point';

            if (in_array($code, ['email', 'pec', 'phone', 'mobile', 'website'], true)) {
                $iconName = $code;
            } elseif (in_array($code, ['linkedin', 'facebook', 'instagram'], true)) {
                $iconGroup = 'social';
                $iconName = $code;
            }

            $href = null;

            if ($category === 'email') {
                $href = 'mailto:' . $value;
            } elseif ($category === 'phone') {
                $href = 'tel:' . preg_replace('/\s+/', '', $value);
            } elseif ($category === 'web') {
                $href = $value;
            }
        @endphp

        <div class="crm-contact-point-row d-flex align-items-start gap-3 py-3">

            {{-- ICONA --}}
            <div class="crm-contact-point-row__icon">
                <x-icon :group="$iconGroup" :name="$iconName" />
            </div>

            {{-- CONTENUTO --}}
            <div class="flex-grow-1 min-w-0">

                {{-- RIGA PRINCIPALE --}}
                <div class="d-flex justify-content-between align-items-start gap-3">

                    <div class="min-w-0">

                        {{-- VALORE --}}
                        <div class="crm-contact-point-row__value">
                            @if ($href)
                                <a href="{{ $href }}" @if($category === 'web') target="_blank" @endif>
                                    {{ $value }}
                                </a>
                            @else
                                {{ $value }}
                            @endif
                        </div>

                        {{-- META --}}
                        <div class="crm-contact-point-row__meta">
                            <span>{{ $type?->name ?? '—' }}</span>

                            @if($usage?->name)
                                <span>•</span>
                                <span>{{ $usage->name }}</span>
                            @endif

                            @if($contactPoint->label)
                                <span>•</span>
                                <span>{{ $contactPoint->label }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- AZIONI --}}
                    <div class="d-flex align-items-center gap-2">
                        @if($contactPoint->is_primary)
                            <span class="badge bg-primary">Primario</span>
                        @endif

                        @if($contactPoint->is_active)
                            <span class="badge bg-success">Attivo</span>
                        @else
                            <span class="badge bg-secondary">Non attivo</span>
                        @endif

                        @include('components.crm.row-actions', [
                            'editModalTarget' => '#contactPointEditModal-' . $contactPoint->id,
                            'delete' => route($destroyRouteName, $contactPoint),
                            'deleteConfirm' => 'Confermi l\'eliminazione di questo recapito?',
                        ])
                    </div>

                </div>
            </div>
        </div>

        <div class="modal fade"
            id="contactPointEditModal-{{ $contactPoint->id }}"
            tabindex="-1"
            aria-labelledby="contactPointEditModalLabel-{{ $contactPoint->id }}"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="contactPointEditModalLabel-{{ $contactPoint->id }}">
                            Modifica recapito
                        </h5>
                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Chiudi"></button>
                    </div>

                    <div class="modal-body">
                        @include('contact-points._form', [
                            'action' => route('contact-points.update', $contactPoint),
                            'method' => 'PUT',
                            'contactPoint' => $contactPoint,
                            'contactTypes' => $contactTypes,
                            'contactUsages' => $contactUsages,
                            'formIdPrefix' => 'contact-point-edit-' . $contactPoint->id,
                            'errorBag' => 'updateContactPoint',
                        ])
                    </div>
                </div>
            </div>
        </div>


        {{-- SEPARATORE --}}
        <hr class="my-0">
    @endforeach
</div>