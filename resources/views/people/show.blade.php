@extends('layouts.app')

@section('title', $person->full_name ?: 'Scheda persona')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column gap-4">

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            @if($person->full_name)
                            <x-crm.avatar
                                :name="$person->full_name"
                                :image="$person->avatar_url"
                                type="person"
                                size="sm"
                            />
                            @endif    
                        <h2 class="h4 mb-0">{{ $person->full_name ?: '—' }}</h2>
                        </div>

                    </div>
                    <div class="d-flex flex-wrap align-items-start gap-2">
                        @include('components.crm.row-actions', [
                            'edit' => route('people.edit', $person),
                            'delete' => route('people.destroy', $person),
                            'deleteConfirm' => 'Confermi l\'eliminazione di questa persona?',
                        ])
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">

            <div class="col-12 col-xl-6">
                @include('people.partials.show.contact-points')
            </div>

            <div class="col-12 col-xl-6">
                @include('people.partials.show.relations')
            </div>
        </div>


    </div>
</div>
@endsection
