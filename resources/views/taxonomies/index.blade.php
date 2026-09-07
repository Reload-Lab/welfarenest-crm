@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-center gap-3 mb-4">
        <div>
            <a href="{{ route('taxonomies.home') }}" class="crm-text-muted small">
                &larr; Torna alle anagrafiche
            </a>
        </div>

        @unless($config['locked'] ?? false)
            <div class="ms-auto d-flex align-items-center gap-2">
                <x-crm.icon-button
                    icon="add"
                    icon-group="actions"
                    title="Nuova voce"
                    data-bs-toggle="modal"
                    data-bs-target="#createTaxonomy"
                />
            </div>
        @endunless
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if($config['locked'] ?? false)
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
            <x-icon group="status" name="warning" />
            <span>
                {{ $config['label_plural'] }} è una tabella di sistema: i valori sono strutturali
                e non possono essere creati, modificati o eliminati da questa sezione.
            </span>
        </div>
    @endif

    <div class="crm-table-card">
        <div class="crm-table-responsive">
            <table class="table crm-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="crm-cell-start">Codice</th>
                        <th>Nome</th>
                        <th>Descrizione</th>
                        @foreach($config['extra_fields'] ?? [] as $field => $fieldConfig)
                            <th>{{ $fieldConfig['label'] }}</th>
                        @endforeach
                        <th class="text-center">Stato</th>
                        <th class="text-end crm-cell-end">Azioni</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($rows as $row)
                        <tr class="crm-table__row">
                            <td class="crm-cell-start">
                                <span class="crm-text-muted">{{ $row->code }}</span>
                            </td>

                            <td>{{ $row->name }}</td>

                            <td>
                                <span class="crm-text-muted">{{ $row->description ?? '—' }}</span>
                            </td>

                            @foreach($config['extra_fields'] ?? [] as $field => $fieldConfig)
                                <td>
                                    @php($optionLabel = $fieldConfig['options'][$row->{$field}] ?? $row->{$field})
                                    <x-crm.tag :label="$optionLabel" variant="default" size="sm" />
                                </td>
                            @endforeach

                            <td class="text-center">
                                @if($row->is_active)
                                    <x-crm.status label="Attivo" variant="success" icon-group="status" icon-name="active" />
                                @else
                                    <x-crm.status label="Non attivo" variant="muted" icon-group="status" icon-name="inactive" />
                                @endif
                            </td>

                            <td class="text-end crm-cell-end">
                                @if($config['locked'] ?? false)
                                    <span class="crm-text-muted small">—</span>
                                @else
                                    <x-crm.row-actions
                                        :edit-modal-target="'#editTaxonomy' . $row->id"
                                        :delete="route('taxonomies.destroy', [$type, $row->id])"
                                        delete-confirm="Confermi l'eliminazione di questa voce?"
                                        :actions="[
                                            [
                                                'route' => route('taxonomies.toggle-active', [$type, $row->id]),
                                                'method' => 'PATCH',
                                                'icon' => $row->is_active ? 'archive' : 'archive-restore',
                                                'label' => $row->is_active ? 'Disattiva' : 'Riattiva',
                                            ],
                                        ]"
                                    />

                                    <div class="modal fade" id="editTaxonomy{{ $row->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Modifica {{ $config['label'] }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                                                </div>

                                                @include('taxonomies._form', [
                                                    'formId' => 'editTaxonomyForm' . $row->id,
                                                    'action' => route('taxonomies.update', [$type, $row->id]),
                                                    'method' => 'PUT',
                                                    'row' => $row,
                                                    'config' => $config,
                                                    'submitLabel' => 'Salva modifiche',
                                                ])
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 5 + count($config['extra_fields'] ?? []) }}" class="p-0">
                                <div class="crm-empty-state">
                                    <div class="crm-empty-state__icon">
                                        <x-icon group="actions" name="search" />
                                    </div>
                                    <h3 class="crm-empty-state__title">Nessuna voce presente</h3>
                                    <p class="crm-empty-state__text">
                                        Non ci sono ancora {{ strtolower($config['label_plural']) }}.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@unless($config['locked'] ?? false)
    <div class="modal fade" id="createTaxonomy" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuova {{ $config['label'] }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>

                @include('taxonomies._form', [
                    'formId' => 'createTaxonomyForm',
                    'action' => route('taxonomies.store', $type),
                    'method' => 'POST',
                    'row' => null,
                    'config' => $config,
                    'submitLabel' => 'Crea',
                ])
            </div>
        </div>
    </div>
@endunless
@endsection
