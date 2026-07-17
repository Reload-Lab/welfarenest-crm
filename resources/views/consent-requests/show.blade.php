@extends('layouts.guest')

@section('title', 'Richiesta consenso')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h1 class="h4 mb-3">Richiesta consenso</h1>

            <p class="text-muted">
                Questa pagina servirà per raccogliere i consensi associati al recapito:
            </p>

            <div class="alert alert-light border">
                {{ $consentRequest->contactPoint?->value ?? 'Recapito non disponibile' }}
            </div>

            <p class="small text-muted mb-0">
                Token valido fino al:
                {{ $consentRequest->expires_at->format('d/m/Y H:i') }}
            </p>
        </div>
    </div>
@endsection