@extends('layouts.wn-plus')

@section('title', 'Il mio profilo')
@section('body_class', 'wn-auth-page')

@section('content')
<div class="container py-5">
    @include('wn-plus.portal.partials.nav', ['active' => 'profile'])

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="wn-auth-card mx-auto p-4 p-md-5 mb-4" style="max-width: 640px;">
        <h2 class="h5 mb-4">I miei dati</h2>

        <dl class="row mb-0">
            <dt class="col-sm-4">Nome</dt>
            <dd class="col-sm-8">{{ $account->full_name }}</dd>

            <dt class="col-sm-4">Email</dt>
            <dd class="col-sm-8">{{ $account->email }}</dd>

            <dt class="col-sm-4">Organizzazione</dt>
            <dd class="col-sm-8">{{ $account->organization?->name ?? $account->organization?->legal_name }}</dd>

            <dt class="col-sm-4">Ruolo</dt>
            <dd class="col-sm-8">{{ $account->role?->name }}</dd>

            <dt class="col-sm-4">Livello</dt>
            <dd class="col-sm-8">{{ $account->level?->name }}</dd>
        </dl>
    </div>

    <div class="wn-auth-card mx-auto p-4 p-md-5 mb-4" style="max-width: 640px;">
        <h2 class="h5 mb-4">Cambia password</h2>

        <form method="POST" action="{{ route('wn-plus.portal.profile.password') }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">Password attuale</label>
                <input type="password" name="current_password"
                       class="form-control wn-auth-input @error('current_password') is-invalid @enderror" required>
                @error('current_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Nuova password</label>
                <input type="password" name="password"
                       class="form-control wn-auth-input @error('password') is-invalid @enderror"
                       minlength="8" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Conferma nuova password</label>
                <input type="password" name="password_confirmation" class="form-control wn-auth-input" minlength="8" required>
            </div>

            <button type="submit" class="wn-auth-submit">Aggiorna password</button>
        </form>
    </div>

    <div class="wn-auth-card mx-auto p-4 p-md-5 mb-4" style="max-width: 640px;">
        <h2 class="h5 mb-4">Privacy e consensi</h2>

        @php
            $privacyConsent = $account->consents->firstWhere('consentType.code', \App\Models\ConsentType::PRIVACY_NOTICE);
            $promoConsent = $account->consents->firstWhere('consentType.code', \App\Models\ConsentType::PROMOTIONAL_EMAILS);
            $imageConsent = $account->consents->firstWhere('consentType.code', \App\Models\ConsentType::IMAGE_DISCLOSURE);
        @endphp

        <div class="mb-3 pb-3 border-bottom">
            <strong>Informativa privacy</strong>
            <div class="text-muted small">
                Accettata il {{ $privacyConsent?->granted_at?->format('d/m/Y') ?? '—' }}
            </div>
        </div>

        <form method="POST" action="{{ route('wn-plus.portal.profile.consents') }}">
            @csrf
            @method('PUT')

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="promotional_emails" value="1" id="promotional_emails"
                       {{ $promoConsent?->status === 'granted' ? 'checked' : '' }}>
                <label class="form-check-label" for="promotional_emails">
                    Acconsento a ricevere comunicazioni informative e promozionali.
                </label>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="image_disclosure" value="1" id="image_disclosure"
                       {{ $imageConsent?->status === 'granted' ? 'checked' : '' }}>
                <label class="form-check-label" for="image_disclosure">
                    Acconsento alla pubblicazione di immagini e contenuti multimediali.
                </label>
            </div>

            <button type="submit" class="wn-auth-submit">Salva consensi</button>
        </form>
    </div>
</div>
@endsection