<div class="crm-contact-point-list">
    @php
        $contactPoints = $contactPoints->sortBy(function ($contactPoint) {
            $code = $contactPoint->contactType?->code;
            $category = $contactPoint->contactType?->category;

            return match (true) {
                in_array($code, ['email', 'pec'], true) => 10,
                in_array($code, ['phone', 'mobile'], true) => 20,
                $category === 'web' => 30,
                $category === 'social' => 40,
                default => 99,
            };
        });
    @endphp

        @if($contactPoints->isEmpty())
            <div class="p-3 text-muted small">
                Nessun recapito presente
            </div>
        
        @else
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

                    $ctaLabel = null;
                    $ctaIcon = null;
                    $isExternal = false;

                    if ($category === 'email') {
                        $href = 'mailto:' . $value;
                        $ctaLabel = 'Email';
                        $ctaIcon = 'send';
                    } elseif ($category === 'phone') {
                        $href = 'tel:' . preg_replace('/\s+/', '', $value);
                        $ctaLabel = 'Chiama';
                        $ctaIcon = 'call';
                    } elseif (in_array($category, ['web', 'social'], true)) {
                        $href = $value;
                        $ctaLabel = 'Apri';
                        $ctaIcon = 'link';
                        $isExternal = true;
                    }


                    if ($category === 'email') {
                        $href = 'mailto:' . $value;
                    } elseif ($category === 'phone') {
                        $href = 'tel:' . preg_replace('/\s+/', '', $value);
                    } elseif ($category === 'web') {
                        $href = $value;
                    }
                @endphp


                <div class="crm-contact-point-card">
                    <div class="d-flex align-items-start gap-3">
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

                                    {{-- 
                                    @if($contactPoint->is_primary)
                                    <x-crm.status
                                        label="Primario"
                                        variant="primary"
                                        icon-group="status"
                                        icon-name="primary"
                                        mode="icon"
                                    />                       
                                    @endif

                                    <x-crm.status
                                        :label="$contactPoint->is_active ? 'Attiva' : 'Non attiva'"
                                        :variant="$contactPoint->is_active ? 'success' : 'muted'"
                                        icon-group="status"
                                        :icon-name="$contactPoint->is_active ? 'active' : 'inactive'"
                                        mode="icon"
                                    />
                                    --}}


                                    {{-- META --}}
                                    <div class="crm-contact-point-row__meta">
                                        {{-- 
                                        <span>{{ $type?->name ?? '—' }}</span>
                                        --}}
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



                                
                                    <button
                                        type="button"
                                        class="crm-contact-action"
                                        data-copy-text="{{ $value }}"
                                        title="Copia"
                                        aria-label="Copia"
                                    >
                                        <x-icon group="actions" name="copy" />
                                    </button>

                                    @if($href && $ctaLabel && $ctaIcon)
                                        <a
                                            href="{{ $href }}"
                                            class="crm-contact-action"
                                            @if($isExternal) target="_blank" rel="noopener" @endif
                                        >
                                            <x-icon group="actions" :name="$ctaIcon" />
                                        </a>
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


            @endforeach
        @endif
</div>