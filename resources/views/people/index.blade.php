@extends('layouts.app')

@php
    $hasAdvancedFilters = false;
@endphp

@section('topbar_title', 'Persone')
@section('topbar_subtitle', 'Gestione anagrafiche persone')

@section('pageHeader')


    <div class="d-flex align-items-center gap-3 mb-4">
        <div>
            {{-- titolo / breadcrumb / altro --}}
        </div>

        <div class="ms-auto d-flex align-items-center gap-2">
            <x-crm.icon-button
                icon="add"
                icon-group="actions"
                title="Nuova Persona"
                href="{{ route('people.create') }}"
            />
        </div>
    </div>

@endsection

@section('content')

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('people.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg">
                    <label for="search" class="form-label fw-semibold">Ricerca</label>
                    <div class="position-relative">
                        <span class="crm-filter-search-icon">
                            <x-icon group="actions" name="search" />
                        </span>

                        <input
                            type="text"
                            name="search"
                            id="search"
                            value="{{ $search }}"
                            class="form-control crm-filter-search-input"
                            placeholder="Nome o cognome"
                        >
                    </div>
                </div>

                <div class="col-12 col-lg-auto">
                    <div class="d-flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="btn btn-outline-secondary btn-inline"
                            id="togglePeopleFilters"
                            aria-expanded="{{ $hasAdvancedFilters ? 'true' : 'false' }}"
                            aria-controls="peopleAdvancedFilters"
                        >
                            <x-icon group="actions" name="filter" />
                            <span>Filtri</span>
                        </button>

                    </div>
                </div>
            </div>

            <div
                id="peopleAdvancedFilters"
                class="mt-3 {{ $hasAdvancedFilters ? '' : 'd-none' }}"
            >
                <div class="crm-filters-panel">
                    <div class="row g-3 align-items-end">

                        <div class="col-12 col-lg">
                            <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                <x-crm.button
                                    type="submit"
                                    icon="filter"
                                    variant="primary"
                                >
                                    Applica filtri
                                </x-crm.button>

                                <x-crm.button
                                    href="{{ route('people.index') }}"
                                    icon="reset"
                                    variant="outline-secondary"
                                >
                                    Reset
                                </x-crm.button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">
        </form>
    </div>
</div>


    <div class="crm-table-card">


        <div class="crm-table-responsive">
            <table class="table crm-table align-middle mb-0">

                <thead>
                    <tr>
                        <th class="crm-cell-start">
                            @include('components.crm.sortable-th', [
                                'label' => 'Nome',
                                'field' => 'last_name',
                                'defaultSort' => 'last_name',
                            ])
                        </th>

                        <th class="text-center">
                            @include('components.crm.sortable-th', [
                                'label' => 'Relazioni',
                                'field' => 'organization_relations_count',
                                'defaultSort' => 'last_name',
                            ])
                        </th>

                        <th class="text-end crm-cell-end">Azioni</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($people as $person)
                        <tr class="crm-table__row">
                            <td class="crm-cell-start">
                                <div class="d-flex align-items-center gap-3">
                                    <x-crm.avatar
                                        :name="$person->display_name"
                                        :image="$person->avatar_url"
                                        type="person"
                                        size="sm"
                                    />

                                    <div class="min-w-0">
                                        <a href="{{ route('people.show', $person) }}"
                                        class="crm-entity-link d-inline-block text-truncate">
                                            {{ $person->display_name }}
                                        </a>
                                    </div>
                                </div>
                            </td>

                            <td class="text-center">
                                <span class="crm-badge crm-badge--muted">
                                    {{ $person->organization_relations_count ?? 0 }}
                                </span>
                            </td>

                            <td class="text-end crm-cell-end">
                                @include('components.crm.row-actions', [
                                    'edit' => route('people.edit', $person),
                                    'delete' => route('people.destroy', $person),
                                    'deleteConfirm' => 'Confermi l\'eliminazione di questa persona?',
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-0">
                                <div class="crm-empty-state">
                                    <div class="crm-empty-state__icon">
                                        <x-icon group="actions" name="search" />
                                    </div>
                                    <h3 class="crm-empty-state__title">Nessuna persona trovata</h3>
                                    <p class="crm-empty-state__text">
                                        Non ci sono persone da mostrare con i filtri correnti.
                                    </p>

                                    <div class="mt-3">
                                        <a href="{{ route('people.create') }}" class="btn btn-primary btn-sm">
                                            Crea la prima persona
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer crm-table-footer">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <form method="GET" action="{{ route('people.index') }}" class="crm-table-footer__left">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="direction" value="{{ $direction }}">

                    <select
                        name="per_page"
                        id="per_page_footer"
                        class="form-select form-select-sm"
                        onchange="this.form.submit()"
                    >
                        <option value="10" {{ (int) $perPage === 10 ? 'selected' : '' }}>10 righe</option>
                        <option value="20" {{ (int) $perPage === 20 ? 'selected' : '' }}>20 righe</option>
                        <option value="50" {{ (int) $perPage === 50 ? 'selected' : '' }}>50 righe</option>
                    </select>
                </form>

                <div class="crm-table-footer__right">
                    @if($people->hasPages())
                        <div class="crm-pagination">
                            {{ $people->links() }}
                        </div>
                    @else
                        <span class="crm-text-muted small">
                            {{ $people->total() }} risultati trovati
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection