@extends('layouts.app')

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <p class="crm-text-muted mb-4">
        Da qui puoi gestire le liste di valori usate nei form di persone e organizzazioni
        (tipologie, ruoli, qualifiche, tipi di indirizzo e di contatto). Le voci contrassegnate
        come "di sistema" sono strutturali e non modificabili da questa sezione.
    </p>

    <div class="row g-3">
        @foreach($types as $item)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <span class="crm-status-icon crm-status-icon--muted">
                                @if($item['icon'])
                                    <x-icon :group="$item['icon']['group']" :name="$item['icon']['name']" />
                                @endif
                            </span>

                            <div class="min-w-0">
                                <h2 class="h6 mb-1">{{ $item['label_plural'] }}</h2>
                                @if($item['locked'])
                                    <x-crm.tag label="Di sistema" variant="warning" size="sm" />
                                @endif
                            </div>

                            <span class="crm-badge crm-badge--muted ms-auto">{{ $item['count'] }}</span>
                        </div>

                        @if($item['description'])
                            <p class="crm-text-muted small mb-4">{{ $item['description'] }}</p>
                        @endif

                        <div class="mt-auto">
                            <x-crm.button
                                href="{{ route('taxonomies.index', $item['slug']) }}"
                                icon="view"
                                variant="outline-secondary"
                                full-width
                            >
                                {{ $item['locked'] ? 'Visualizza' : 'Gestisci' }}
                            </x-crm.button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
