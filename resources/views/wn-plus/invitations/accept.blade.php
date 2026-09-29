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

            @php
                $basicCode = \App\Models\ConsentType::PROFILE_VISIBILITY_BASIC;
                $emailCode = \App\Models\ConsentType::PROFILE_VISIBILITY_EMAIL;
                $phoneCode = \App\Models\ConsentType::PROFILE_VISIBILITY_PHONE;
                $photoCode = \App\Models\ConsentType::PROFILE_VISIBILITY_PHOTO;

                // Ordine e testi seguono il blocco "Raccolta delle scelte e dei consensi"
                // dell'informativa del ruolo (12 referente / 13 membro).
                $labelFor = fn (string $code) => \App\Support\ConsentStatement::for (
                    $code,
                    $versionCode,
                    $consentVersions->get($code),
                );
            @endphp

            <div class="wn-consent-card is-required mb-3">
                <div class="d-flex gap-3">
                    <span class="wn-consent-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 3L19 6V11C19 15.5 16.2 19.5 12 21C7.8 19.5 5 15.5 5 11V6L12 3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <path d="M9 12L11 14L15.5 9.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>

                    <div class="form-check wn-consent-check pt-1">
                        <input
                            class="form-check-input @error('privacy_base') is-invalid @enderror"
                            type="checkbox"
                            name="privacy_base"
                            value="1"
                            id="privacy_base"
                            required
                        >
                        <label class="form-check-label" for="privacy_base">
                            <strong>{{ $labelFor('privacy_notice') }}</strong>
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
                            <path d="M12 12C14.2 12 16 10.2 16 8C16 5.8 14.2 4 12 4C9.8 4 8 5.8 8 8C8 10.2 9.8 12 12 12Z" stroke="currentColor" stroke-width="2"/>
                            <path d="M4 20C4 16.7 7.6 14 12 14C16.4 14 20 16.7 20 20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </span>

                    <div class="pt-1 w-100">
                        <p class="fw-semibold mb-2">Visibilità nella community</p>

                        <div class="form-check form-switch wn-consent-switch">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   name="{{ $basicCode }}" value="1" id="{{ $basicCode }}"
                                   data-wn-visibility-master
                                   @checked(old($basicCode))>
                            <label class="form-check-label" for="{{ $basicCode }}">
                                {{ $labelFor($basicCode) }}
                            </label>
                        </div>

                        <div class="ps-4">
                            @foreach ([$emailCode, $phoneCode, $photoCode] as $code)
                                @php $dependsOnBasic = $code !== $photoCode; @endphp

                                <div class="form-check form-switch wn-consent-switch mt-3">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           name="{{ $code }}" value="1" id="{{ $code }}"
                                           @if ($dependsOnBasic) data-wn-visibility-dependent @endif
                                           @checked(old($code))
                                           @if ($dependsOnBasic && ! old($basicCode)) disabled @endif>
                                    <label class="form-check-label" for="{{ $code }}">
                                        {{ $labelFor($code) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="wn-consent-card mb-3">
                <div class="d-flex gap-3">
                    <span class="wn-consent-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M21 4L10 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M21 4L14 21L10 15L3 11L21 4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        </svg>
                    </span>

                    <div class="form-check form-switch wn-consent-switch pt-1">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            name="service_updates"
                            value="1"
                            id="service_updates"
                            @checked(old('service_updates'))
                        >
                        <label class="form-check-label" for="service_updates">
                            {{ $labelFor('service_updates') }}<br>
                            <span class="text-muted">
                                Gli avvisi di sicurezza, accesso e gestione del servizio restano necessari e non dipendono da questa scelta.
                            </span>
                        </label>
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

                    <div class="form-check form-switch wn-consent-switch pt-1">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            name="image_disclosure"
                            value="1"
                            id="image_disclosure"
                            @checked(old('image_disclosure'))
                        >
                        <label class="form-check-label" for="image_disclosure">
                            {{ $labelFor('image_disclosure') }}
                        </label>
                    </div>
                </div>
            </div>

            <div class="wn-consent-card mb-4">
                <div class="d-flex gap-3">
                    <span class="wn-consent-icon">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M8 4H16C17.1 4 18 4.9 18 6V20L12 17L6 20V6C6 4.9 6.9 4 8 4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        </svg>
                    </span>

                    <div class="form-check form-switch wn-consent-switch pt-1">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            name="identifiable_surveys"
                            value="1"
                            id="identifiable_surveys"
                            @checked(old('identifiable_surveys'))
                        >
                        <label class="form-check-label" for="identifiable_surveys">
                            {{ $labelFor('identifiable_surveys') }}
                        </label>
                    </div>
                </div>
            </div>

            <p class="text-muted small mb-4">
                Tutte le scelte facoltative sono modificabili in qualsiasi momento dall’area riservata.
            </p>

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

@push('scripts')
<script>
    // La visibilità di email e telefono è attivabile solo con il profilo base
    // visibile. Il vincolo è comunque riapplicato lato server.
    (function () {
        const master = document.querySelector('[data-wn-visibility-master]');
        const dependents = document.querySelectorAll('[data-wn-visibility-dependent]');

        if (! master) {
            return;
        }

        master.addEventListener('change', function () {
            dependents.forEach(function (input) {
                input.disabled = ! master.checked;

                if (! master.checked) {
                    input.checked = false;
                }
            });
        });
    })();
</script>
@endpush