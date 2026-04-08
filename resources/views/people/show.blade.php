@extends('layouts.app')

@section('title', $person->full_name ?: 'Scheda persona')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column gap-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
            </div>
        @endif

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('people.index') }}">Persone</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $person->full_name ?: 'Scheda persona' }}
                        </li>
                    </ol>
                </nav>

                <h1 class="h3 mb-0">Scheda persona</h1>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('people.index') }}" class="btn btn-outline-secondary">
                    Torna all'elenco
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <h2 class="h4 mb-0">{{ $person->full_name ?: '—' }}</h2>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-12 col-md-auto">
                                <div class="small text-muted">ID</div>
                                <div class="fw-semibold">#{{ $person->id }}</div>
                            </div>

                            <div class="col-12 col-md-auto">
                                <div class="small text-muted">Creata il</div>
                                <div class="fw-semibold">
                                    {{ optional($person->created_at)->format('d/m/Y H:i') ?? '—' }}
                                </div>
                            </div>

                            <div class="col-12 col-md-auto">
                                <div class="small text-muted">Ultimo aggiornamento</div>
                                <div class="fw-semibold">
                                    {{ optional($person->updated_at)->format('d/m/Y H:i') ?? '—' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-start gap-2">
                        <a href="{{ route('people.edit', $person) }}" class="btn btn-primary">
                            Modifica
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <h3 class="h5 mb-0">Dati principali</h3>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-12 col-md-6">
                                <div class="small text-muted mb-1">Nome</div>
                                <div class="fw-semibold">{{ $person->first_name ?: '—' }}</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="small text-muted mb-1">Cognome</div>
                                <div class="fw-semibold">{{ $person->last_name ?: '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                @include('people.partials.show.relations')
            </div>
        </div>
    </div>
</div>
@endsection
