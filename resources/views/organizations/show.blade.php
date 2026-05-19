@extends('layouts.app')

@section('title', $organization->name ?: $organization->legal_name)

@section('content')
<div class="container-fluid py-4">


    <div class="d-flex flex-column gap-4">
        
        @include('organizations.partials.show.header')

        <div class="row g-4">
            <div class="col-12 col-xl-7">
                @include('organizations.partials.show.main-data')
            </div>

            <div class="col-12 col-xl-5">
                @include('organizations.partials.show.contact-points')
                
                @include('notes._section', [
                    'organization' => $organization,
                    'notes' => $organization->notes,
                ])

            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-7">
                @include('organizations.partials.show.addresses')
            </div>

            <div class="col-12 col-xl-5">
                @include('notes._section', [
                    'organization' => $organization,
                    'notes' => $organization->notes,
                ])
            </div>
        </div>

        @include('organizations.partials.show.people')        

    </div>
</div>
@endsection