@if ($contactPoints->isEmpty())
    <div class="text-center py-4 text-muted">
        <p class="mb-0">Nessun recapito inserito.</p>
    </div>
@else
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Valore</th>
                    <th>Uso</th>
                    <th>Etichetta</th>
                    <th class="text-center">Primario</th>
                    <th class="text-center">Stato</th>
                    <th class="text-end">Azioni</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contactPoints as $contactPoint)
                    @php
                        $type = $contactPoint->contactType;
                        $usage = $contactPoint->contactUsage;
                        $category = $type->category ?? null;
                        $value = $contactPoint->value;
                    @endphp

                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $type?->name ?? '—' }}</div>
                            @if($category)
                                <div class="small text-muted">{{ $category }}</div>
                            @endif
                        </td>

                        <td>
                            @if ($category === 'email')
                                <a href="mailto:{{ $value }}">{{ $value }}</a>
                            @elseif ($category === 'phone')
                                <a href="tel:{{ preg_replace('/\s+/', '', $value) }}">{{ $value }}</a>
                            @elseif ($category === 'web')
                                <a href="{{ $value }}" target="_blank" rel="noopener noreferrer">{{ $value }}</a>
                            @else
                                <span>{{ $value }}</span>
                            @endif
                        </td>

                        <td>{{ $usage?->name ?? '—' }}</td>
                        <td>{{ $contactPoint->label ?: '—' }}</td>

                        <td class="text-center">
                            @if($contactPoint->is_primary)
                                <span class="badge text-bg-primary">Sì</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($contactPoint->is_active)
                                <span class="badge text-bg-success">Attivo</span>
                            @else
                                <span class="badge text-bg-secondary">Non attivo</span>
                            @endif
                        </td>

                        <td class="text-end">
                            <form
                                action="{{ route($destroyRouteName, $contactPoint) }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirm('Eliminare questo recapito?')"
                            >
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <x-icon group="actions" name="trash" />
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif