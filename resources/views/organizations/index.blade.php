@extends('layouts.app')

@php
    $hasAdvancedFilters = filled($status);

    $showRoute = match ($indexRoute) {
        'clients.index' => 'clients.show',
        'suppliers.index' => 'suppliers.show',
        default => 'organizations.show',
    };
@endphp


@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-center gap-3 mb-4">
        <div>
            {{-- titolo / breadcrumb / altro --}}
        </div>

        <div class="ms-auto d-flex align-items-center gap-2">
            <x-crm.icon-button
                icon="add"
                icon-group="actions"
                title="Nuova Organizzazione"
                href="{{ route('organizations.create') }}"
            />
        </div>
    </div>


    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm" role="alert">
            {{ session('success') }}
        </div>
    @endif





<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route($indexRoute) }}">
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
                            placeholder="Nome, ragione sociale, P.IVA, codice fiscale..."
                        >
                    </div>
                </div>

                <div class="col-12 col-lg-auto">
                    <div class="d-flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="btn btn-outline-secondary btn-inline"
                            id="toggleOrganizationFilters"
                            aria-expanded="{{ $hasAdvancedFilters ? 'true' : 'false' }}"
                            aria-controls="organizationAdvancedFilters"
                        >
                            <x-icon group="actions" name="filter" />
                            <span>Filtri</span>
                        </button>
                    </div>
                </div>
            </div>

<div
    id="organizationAdvancedFilters"
    class="mt-3 {{ $hasAdvancedFilters ? '' : 'd-none' }}"
>
    <div class="crm-filters-panel">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-4 col-lg-3">
                <label for="status" class="form-label fw-semibold">Stato</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Tutti</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Attivi</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Non attivi</option>
                </select>
            </div>

            <div class="col-12 col-lg">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <x-crm.button type="submit" icon="filter" variant="primary">
                        Applica filtri
                    </x-crm.button>

                    <x-crm.button
                        href="{{ route($indexRoute) }}"
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
                            'field' => 'name'
                        ])
                    </th>

                    <th>Tipologia organizzazione</th>

                    <th class="text-center">
                        @include('components.crm.sortable-th', [
                            'label' => 'Relazioni',
                            'field' => 'relations_count'
                        ])
                    </th>

                    <th class="text-end crm-cell-end">Azioni</th>
                </tr>
            </thead>

            <tbody>
                @forelse($organizations as $organization)
                    <tr class="crm-table__row">
                        <td class="crm-cell-start">
                            <div class="d-flex align-items-center gap-3">
                                <x-crm.avatar
                                    :name="$organization->display_name"
                                    :image="$organization->avatar_url"
                                    type="organization"
                                    size="sm"
                                />

                                <div class="min-w-0">
                                    <a href="{{ route($showRoute, $organization) }}"
                                       class="crm-entity-link d-inline-block text-truncate">
                                        {{ $organization->display_name }}
                                    </a>
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="crm-text-muted">
                                {{ $organization->organizationType?->name ?? '—' }}
                            </span>
                        </td>

                        <td class="text-center">
                            <span class="crm-badge crm-badge--muted">
                                {{ $organization->relations_count ?? 0 }}
                            </span>
                        </td>

                        <td class="text-end crm-cell-end">
                            <x-crm.row-actions
                                :view="route($showRoute, $organization)"
                                :edit="route('organizations.edit', $organization)"
                                :delete="route('organizations.destroy', $organization)"
                                delete-confirm="Confermi l'eliminazione di questa organizzazione?"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-0">
                            <div class="crm-empty-state">
                                <div class="crm-empty-state__icon">
                                    <x-icon group="actions" name="search" />
                                </div>
                                <h3 class="crm-empty-state__title">Nessuna organizzazione trovata</h3>
                                <p class="crm-empty-state__text">
                                    Non ci sono organizzazioni da mostrare con i filtri correnti.
                                </p>

                                <div class="mt-3">
                                    <x-crm.button
                                        href="{{ route('organizations.create') }}"
                                        icon="add"
                                        variant="primary"
                                    >
                                        {{ $createLabel }}
                                    </x-crm.button>
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

            <form method="GET" action="{{ route($indexRoute) }}" class="d-flex align-items-center gap-2">
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="status" value="{{ $status }}">
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

            <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                @if($organizations->hasPages())
                    <div class="crm-pagination">
                        {{ $organizations->links() }}
                    </div>
                @else
                     <span class="crm-text-muted small">
                        {{ $organizations->total() }} risultati trovati
                    </span>
                @endif
            </div>

        </div>
    </div>


</div>


</div>
@endsection