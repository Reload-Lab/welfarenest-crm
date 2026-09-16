@extends('layouts.app')

@section('title', 'Utenti WN+')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Utenti WN+</h1>
            <p class="text-muted mb-0">
                Gestione degli account abilitati all’accesso a Welfare Nest Plus.
            </p>
        </div>


        <div class="ms-auto d-flex align-items-center gap-2">
            <form method="GET" action="{{ route('wn-plus.accounts.index') }}" class="d-flex align-items-center gap-2">
                <input
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    class="form-control form-control-sm"
                    placeholder="Cerca per nome o email..."
                    style="min-width: 220px;"
                >
                <button type="submit" class="btn btn-sm btn-outline-secondary">Cerca</button>

                @if($search !== '')
                    <a href="{{ route('wn-plus.accounts.index') }}" class="btn btn-sm btn-link text-muted">Cancella</a>
                @endif
            </form>

            <x-crm.icon-button
                icon="add"
                icon-group="actions"
                title="Nuova Persona"
                href="{{ route('wn-plus.accounts.create') }}"
            />
        </div>
    </div>

    @include('wn-plus.accounts._table', ['managers' => $managers, 'orphanUsers' => $orphanUsers, 'search' => $search])
@endsection



 