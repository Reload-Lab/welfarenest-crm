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
            <x-crm.icon-button
                icon="add"
                icon-group="actions"
                title="Nuova Persona"
                href="{{ route('wn-plus.accounts.create') }}"
            />
        </div>
    </div>

    @include('wn-plus.accounts._table', ['accounts' => $accounts])
@endsection



 