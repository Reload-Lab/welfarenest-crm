@if ($contactPoints->isEmpty())
    <div class="text-center py-4 text-muted">
        <div class="mb-2">
            <x-icon group="contact" name="contact_point" />
        </div>
        <p class="mb-0">Nessun recapito inserito.</p>
    </div>
@else
    <div class="row g-3">
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

            <div class="col-12 col-md-6 col-xl-4">
                <div class="crm-contact-point-card h-100">
                    <div class="d-flex align-items-start gap-3">
                        <div class="crm-contact-point-card__icon">
                            <x-icon :group="$iconGroup" :name="$iconName" />
                        </div>

                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div class="min-w-0 flex-grow-1">
                                    <div class="crm-contact-point-card__value">
                                        @if ($href)
                                            <a
                                                href="{{ $href }}"
                                                @if($category === 'web') target="_blank" rel="noopener noreferrer" @endif
                                            >
                                                {{ $value }}
                                            </a>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </div>

                                    <div class="crm-contact-point-card__meta">
                                        <span>{{ $type?->name ?? '—' }}</span>

                                        @if($usage?->name)
                                            <span class="crm-contact-point-card__separator">•</span>
                                            <span>{{ $usage->name }}</span>
                                        @endif

                                        @if($contactPoint->label)
                                            <span class="crm-contact-point-card__separator">•</span>
                                            <span>{{ $contactPoint->label }}</span>
                                        @endif
                                    </div>
                                </div>

                                <form
                                    action="{{ route($destroyRouteName, $contactPoint) }}"
                                    method="POST"
                                    onsubmit="return confirm('Eliminare questo recapito?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Elimina"
                                        aria-label="Elimina"
                                    >
                                        <x-icon group="actions" name="trash" />
                                    </button>
                                </form>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-3">
                                @if($contactPoint->is_primary)
                                    <span class="crm-inline-badge crm-inline-badge--primary">
                                        <x-icon group="status" name="primary" />
                                        Primario
                                    </span>
                                @endif

                                @if($contactPoint->is_active)
                                    <span class="crm-inline-badge crm-inline-badge--success">
                                        <x-icon group="status" name="active" />
                                        Attivo
                                    </span>
                                @else
                                    <span class="crm-inline-badge crm-inline-badge--muted">
                                        <x-icon group="status" name="inactive" />
                                        Non attivo
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif