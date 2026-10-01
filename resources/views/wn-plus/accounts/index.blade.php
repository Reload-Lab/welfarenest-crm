@extends('layouts.app')

@section('title', 'Utenti WN+')

@section('pageHeader')

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h1 class="crm-page-title">Utenti WN+</h1>
            <p class="crm-page-subtitle">
                Gestione degli account abilitati all’accesso a Welfare Nest Plus,
                raggruppati per organizzazione.
            </p>
        </div>

        <div class="ms-auto d-flex align-items-center gap-2">
            <x-crm.icon-button
                icon="add"
                icon-group="actions"
                title="Nuovo referente WN+"
                href="{{ route('wn-plus.accounts.create') }}"
            />
        </div>
    </div>

@endsection

@section('content')

    {{-- Stessa card di ricerca di Organizzazioni, Persone e Consensi: campo
         etichettato con la lente dentro, bottone Cerca primario alla stessa altezza. --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('wn-plus.accounts.index') }}">
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
                                placeholder="Nome, cognome, email o organizzazione"
                            >
                        </div>
                    </div>

                    <div class="col-12 col-lg-auto">
                        <div class="d-flex flex-wrap gap-2">
                            <x-crm.button
                                type="submit"
                                icon="search"
                                variant="primary"
                            >
                                Cerca
                            </x-crm.button>

                            @if($search !== '')
                                <x-crm.button
                                    href="{{ route('wn-plus.accounts.index') }}"
                                    icon="reset"
                                    variant="outline-secondary"
                                >
                                    Reset
                                </x-crm.button>
                            @endif
                        </div>
                    </div>
                </div>

                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
            </form>
        </div>
    </div>

    @include('wn-plus.accounts._table', [
        'organizations' => $organizations,
        'groups' => $groups,
        'unassignedGroup' => $unassignedGroup,
        'search' => $search,
        'sort' => $sort,
        'direction' => $direction,
        'perPage' => $perPage,
    ])
@endsection
