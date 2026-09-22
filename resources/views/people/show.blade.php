@extends('layouts.app')

@section('title', $person->full_name ?: 'Scheda persona')

@php
    use App\Models\ConsentType;
@endphp



@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column gap-4">

        {{-- Gli errori di invio della richiesta di consenso sono mostrati dentro
             la modale "Privacy e consensi", accanto all'azione che li genera. --}}

        <div class="card border-0 shadow-sm crm-card--header-actions">
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


                    <div class="d-flex align-items-center gap-2">

                        {{--
                            Nessun pulsante di invio qui: invio, sollecito e rinnovo
                            della richiesta di consenso vivono dentro la modale
                            "Privacy e consensi", dove c'è il contesto dello stato.
                            Questo badge è l'unico punto d'accesso.
                        --}}
                        <button
                            type="button"
                            class="crm-status-badge crm-status-badge--{{ $person->consentRequestStateVariant(ConsentType::PRIVACY_NOTICE) }} crm-status-badge--xl border-0"
                            title="{{ $person->consentRequestStateLabel(ConsentType::PRIVACY_NOTICE) }}"
                            aria-label="{{ $person->consentRequestStateLabel(ConsentType::PRIVACY_NOTICE) }}"
                            data-bs-toggle="modal"
                            data-bs-target="#personConsentsModal">

                            <x-icon group="entities" name="consent" />

                        </button>

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

@include('people.partials.show.consents-modal', [
    'person' => $person,
])

@endsection

@push('scripts')
    @if(session('openConsentsModal'))
        {{-- L'azione di invio/sollecito parte da dentro la modale e fa redirect:
             riapriamo la modale per mostrare subito lo stato aggiornato. --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalElement = document.getElementById('personConsentsModal');

                if (modalElement && window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
                }
            });
        </script>
    @endif
@endpush


