@extends('layouts.app')

@section('topbar_title', 'Persone')
@section('topbar_subtitle', 'Creazione anagrafica persona')

@section('pageHeader')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <h1 class="crm-page-title">Nuova persona</h1>
            <p class="crm-page-subtitle mb-0">
                Inserisci i dati anagrafici principali
            </p>
        </div>
    </div>
@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">
            <div class="fw-semibold mb-1">Controlla i dati inseriti</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('people.store') }}">
        @csrf

        @include('people.partials.form', [
            'submitLabel' => 'Salva persona',
        ])
    </form>
@endsection
