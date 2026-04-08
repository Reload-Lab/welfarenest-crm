@extends('layouts.app')

@section('topbar_title', 'Clienti')
@section('topbar_subtitle', 'Creazione anagrafica cliente')

@section('pageHeader')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <h1 class="crm-page-title">Nuovo cliente</h1>
            <p class="crm-page-subtitle mb-0">
                Inserisci i dati principali dell'organizzazione
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

    <form method="POST" action="{{ route('organizations.store') }}">
        @csrf

        @include('organizations.partials.form', [
            'submitLabel' => 'Salva cliente'
        ])
    </form>
@endsection