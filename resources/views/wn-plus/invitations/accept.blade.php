@extends('layouts.guest')

@section('title', 'Attiva account WN+')

@section('content')
    <div class="container py-5">
        <div class="card mx-auto" style="max-width: 520px;">
            <div class="card-body">
                <h1 class="h4 mb-3">Attiva il tuo account WN+</h1>

                <p class="text-muted">
                    Ciao {{ $invitation->account->full_name }},
                    stai attivando l’accesso a Welfare Nest Plus.
                </p>

                <dl>
                    <dt>Email</dt>
                    <dd>{{ $invitation->account->email }}</dd>

                    <dt>Organizzazione</dt>
                    <dd>{{ $invitation->account->organization?->name ?? $invitation->account->organization?->legal_name }}</dd>
                </dl>

                <div class="alert alert-info mb-0">
                    Nel prossimo passaggio qui inseriremo password e consensi.
                </div>
            </div>
        </div>
    </div>
@endsection