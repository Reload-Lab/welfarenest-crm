@extends('layouts.app')

@section('title', $organization->name ?: $organization->legal_name)

@section('content')
<div class="container-fluid py-4">


    <div class="d-flex flex-column gap-4">

        {{-- Breadcrumb / toolbar --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            
        <x-crm.button
            href="{{ route('organizations.index') }}"
            icon-group="navigation"
            icon="menu"
            class="ms-auto"
        >
            Torna all'elenco
        </x-crm.button>

        

        </div>

        
        
        @include('organizations.partials.show.header')

        <div class="row g-4">
            <div class="col-12 col-xl-7">
                @include('organizations.partials.show.main-data')
            </div>

            <div class="col-12 col-xl-5">
                @include('organizations.partials.show.contact-points')
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-7">
                @include('organizations.partials.show.addresses')
            </div>

            <div class="col-12 col-xl-5">
                @include('organizations.partials.show.notes')
            </div>
        </div>

        @include('organizations.partials.show.people')        
        
              
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