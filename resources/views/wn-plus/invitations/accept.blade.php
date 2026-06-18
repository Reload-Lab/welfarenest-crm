@extends('layouts.wn-plus')

@section('title', 'Attivazione account WN+')
@section('body_class', 'wn-auth-page')
@section('full_page', 'true')

@section('content')
@php
    $account = $invitation->account;
    $organizationName = $account->organization?->name
        ?? $account->organization?->legal_name
        ?? 'Welfare Nest';
@endphp

<div class="container py-5">
    <div class="text-center mb-5">
        <img
            src="/images/logo-wn-plus.svg"
            alt="Welfare Nest"
            class="wn-auth-logo"
        >
    </div>

    <div class="wn-auth-card mx-auto p-4 p-md-5">

        <div class="row align-items-center g-4 g-lg-5 mb-4">
            <div class="col-md-5 text-center">
                <div class="wn-auth-illustration mx-auto">
                    <svg width="112" height="112" viewBox="0 0 120 120" fill="none" aria-hidden="true">
                        <path d="M18 45L60 76L102 45V95H18V45Z" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/>
                        <path d="M18 45L60 14L102 45" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/>
                        <path d="M42 39H78M42 51H72M42 63H66" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                        <circle cx="88" cy="74" r="16" fill="currentColor"/>
                        <path d="M80 74L86 80L97 67" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>

            <div class="col-md-7">
                <h1 class="wn-auth-title mb-4">
                    Attiva il tuo<br>
                    account <strong>WN+</strong>
                </h1>

                <p class="wn-auth-text mb-0">
                    Ciao {{ $account->full_name }},
                    imposta una password sicura e conferma i consensi per accedere
                    a Welfare Nest Plus.
                </p>
            </div>
        </div>

        <div class="wn-auth-info row g-4 py-4 mb-4">
            <div class="col-md-6 d-flex gap-3 align-items-center">
                <span class="wn-auth-info-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 6H20V18H4V6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        <path d="M4 7L12 13L20 7" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div>
                    <div class="fw-bold">Email</div>
                    <div>{{ $account->email }}</div>
                </div>
            </div>

            <div class="col-md-6 d-flex gap-3 align-items-center">
                <span class="wn-auth-info-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 21V7L12 3L19 7V21" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        <path d="M9 21V13H15V21" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        <path d="M9 8H9.01M15 8H15.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                </span>
                <div>
                    <div class="fw-bold">Organizzazione</div>
                    <div>{{ $organizationName }}</div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('wn-plus.invitations.complete', $invitation->token) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input
                    type="password"
                    name="password"
                    class="form-control wn-auth-input @error('password') is-invalid @enderror"
                    placeholder="Inserisci la password"
                    required
                    minlength="8"
                >
                <div class="form-text">
                    Minimo 8 caratteri, con maiuscole, minuscole, numeri e simboli.
                </div>

                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Conferma password</label>
                <input
                    type="password"
                    name="password_confirmation"
                    class="form-control wn-auth-input"
                    placeholder="Conferma la password"
                    required
                    minlength="8"
                >
            </div>

            <div class="wn-consent-card is-required mb-3">
                <div class="d-flex gap-3">
                    <span class="wn-consent-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 3L19 6V11C19 15.5 16.2 19.5 12 21C7.8 19.5 5 15.5 5 11V6L12 3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <path d="M9 12L11 14L15.5 9.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>

                    <div class="form-check pt-1">
                        <input
                            class="form-check-input @error('privacy_base') is-invalid @enderror"
                            type="checkbox"
                            name="privacy_base"
                            value="1"
                            id="privacy_base"
                            required
                        >
                        <label class="form-check-label" for="privacy_base">
                            <strong>Dichiaro di aver letto l’informativa privacy.</strong><br>
                            <span class="text-muted">
                                Ho letto e compreso l’informativa sul trattamento dei dati personali.
                            </span>
                        </label>

                        @error('privacy_base')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="wn-consent-card mb-3">
                <div class="d-flex gap-3">
                    <span class="wn-consent-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 14H8L17 19V5L8 10H4V14Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <path d="M19 9C20 10 20 14 19 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </span>

                    <div class="form-check pt-1">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="image_disclosure"
                            value="1"
                            id="image_disclosure"
                        >
                        <label class="form-check-label" for="image_disclosure">
                            <strong>Acconsento alla pubblicazione di immagini e contenuti multimediali.</strong><br>
                            <span class="text-muted">
                                Autorizzo la pubblicazione di immagini e contenuti che mi ritraggono su materiali e canali istituzionali.
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="wn-consent-card mb-4">
                <div class="d-flex gap-3">
                    <span class="wn-consent-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M21 4L10 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M21 4L14 21L10 15L3 11L21 4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        </svg>
                    </span>

                    <div class="form-check pt-1">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="promotional_emails"
                            value="1"
                            id="promotional_emails"
                        >
                        <label class="form-check-label" for="promotional_emails">
                            <strong>Acconsento a ricevere comunicazioni informative e promozionali.</strong><br>
                            <span class="text-muted">
                                Autorizzo l’invio di comunicazioni su iniziative, eventi, novità e offerte di Welfare Nest Plus.
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="wn-auth-submit w-100 mb-4">
                Attiva account
            </button>

            <div class="text-center wn-auth-safe">
                I tuoi dati sono al sicuro con noi.
            </div>
        </form>
    </div>

    <footer class="wn-auth-footer text-center mt-5 small">
        © {{ date('Y') }} Welfare Nest Plus. Tutti i diritti riservati.
        <span class="mx-3 d-none d-md-inline">|</span>
        <span class="d-block d-md-inline">
            Assistenza: <a href="mailto:info@welfarenest.it">info@welfarenest.it</a>
        </span>
    </footer>
</div>
@endsection