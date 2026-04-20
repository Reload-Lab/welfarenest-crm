@extends('layouts.app')

@section('topbar_title', 'Persone')
@section('topbar_subtitle', 'Gestione anagrafiche persone')

@section('pageHeader')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <h1 class="crm-page-title">Persone</h1>
            <p class="crm-page-subtitle mb-0">
                Elenco e gestione delle anagrafiche personali
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <x-crm.button
                href="{{ route('people.create') }}"
                icon="add"
            >
                Nuova Persona
            </x-crm.button>
        </div>

    </div>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('people.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-7">
                        <label for="search" class="form-label fw-semibold">Ricerca</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            class="form-control"
                            placeholder="Nome o cognome"
                            value="{{ $search }}"
                        >
                    </div>

                    <div class="col-12 col-md-4 col-lg-2">
                        <label for="per_page" class="form-label fw-semibold">Righe</label>
                        <select name="per_page" id="per_page" class="form-select">
                            <option value="10" {{ (int) $perPage === 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ (int) $perPage === 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ (int) $perPage === 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-8 col-lg-3">
                        <div class="d-flex gap-2">

                            <x-crm.button 
                                type="submit"
                                icon="filter"
                            >
                                Filtra
                            </x-crm.button>


                            <a href="{{ route('people.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-2">
                                <x-icon group="actions" name="reset" />
                                <span>Reset</span>
                            </a>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
            </form>
        </div>
    </div>

    <div class="crm-table-card">
        <div class="crm-table-card__header">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div>
                    <h2 class="crm-table-card__title">Anagrafiche persone</h2>
                    <p class="crm-table-card__subtitle">
                        {{ $people->total() }} risultati trovati
                    </p>
                </div>
            </div>
        </div>

        <div class="crm-table-responsive">
            <table class="table crm-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="crm-cell-start">
                            @include('components.crm.sortable-th', [
                                'label' => 'Nome',
                                'field' => 'first_name',
                                'defaultSort' => 'last_name',
                            ])
                        </th>

                        <th>
                            @include('components.crm.sortable-th', [
                                'label' => 'Cognome',
                                'field' => 'last_name',
                                'defaultSort' => 'last_name',
                            ])
                        </th>

                        <th>
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
                                <div class="d-flex align-items-center gap-2">
                                    <x-crm.avatar
                                        :name="$person->display_name"
                                        :image="$person->avatar_url"
                                        type="person"
                                        size="sm"
                                    />

                                    <span class="text-truncate">
                                        {{ $person->first_name }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <a href="{{ route('people.show', $person) }}"
                                   class="crm-entity-link">
                                    {{ $person->last_name ?: '—' }}
                                </a>
                            </td>

                            <td>
                                <span class="crm-text-muted">
                                    {{ $person->organization_relations_count ?? 0 }}
                                </span>
                            </td>

                            <td class="text-end crm-cell-end">
                                <x-crm.row-actions
                                    :view="route('people.show', $person)"
                                    :edit="route('people.edit', $person)"
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

        @if($people->hasPages())
            <div class="card-footer bg-white border-0 px-4 py-3">
                {{ $people->links() }}
            </div>
        @endif
    </div>
@endsection