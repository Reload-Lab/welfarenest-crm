@extends('layouts.app')

@section('title', $organization->name ?: $organization->legal_name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column gap-4">

        {{-- Breadcrumb / toolbar --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('organizations.index') }}">Organizzazioni</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $organization->name ?: $organization->legal_name ?: 'Scheda organizzazione' }}
                        </li>
                    </ol>
                </nav>

                <h1 class="h3 mb-0">
                    Scheda organizzazione
                </h1>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('organizations.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                    <span class="ms-1">Torna all'elenco</span>
                </a>
            </div>
        </div>

        @include('organizations.partials.show.header')
        
        <div class="row g-4">
            <div class="col-12 col-xl-8 d-flex flex-column gap-4">
                @include('organizations.partials.show.main-data')
                @include('organizations.partials.show.people')
                @include('organizations.partials.show.contact-points')
            </div>

            <div class="col-12 col-xl-4 d-flex flex-column gap-4">
                @include('organizations.partials.show.addresses')
                @include('organizations.partials.show.notes')
            </div>
        </div>
    </div>
</div>
@endsection