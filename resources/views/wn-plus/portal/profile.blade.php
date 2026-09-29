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
            $basicCode = \App\Models\ConsentType::PROFILE_VISIBILITY_BASIC;
            $emailCode = \App\Models\ConsentType::PROFILE_VISIBILITY_EMAIL;
            $phoneCode = \App\Models\ConsentType::PROFILE_VISIBILITY_PHONE;
            $photoCode = \App\Models\ConsentType::PROFILE_VISIBILITY_PHOTO;

            $privacyConsent = $account->consents->firstWhere('consentType.code', \App\Models\ConsentType::PRIVACY_NOTICE);

            // Ultimo consenso registrato per tipo: i consensi sono eventi, quindi per ogni
            // tipo conta il più recente.
            $latestConsents = $account->consents
                ->sortByDesc('created_at')
                ->unique(fn ($consent) => $consent->consentType?->code)
                ->keyBy(fn ($consent) => $consent->consentType?->code);

            $isGranted = fn (string $code) => ($latestConsents[$code] ?? null)?->status === 'granted';

            // Le tre visibilità di contatto stanno sotto quella del profilo base, da cui
            // email e telefono dipendono.
            $visibilityGroup = [
                $emailCode,
                $phoneCode,
                $photoCode,
            ];

            $otherConsents = [
                'service_updates',
                'image_disclosure',
                'identifiable_surveys',
            ];

            // I testi stanno in config/consent_statements.php, nella variante specifica
            // dell'informativa di questo ruolo (12 referente / 13 membro).
            $labelFor = fn (string $code) => \App\Support\ConsentStatement::for (
                $code,
                $versionCode,
                $consentVersions->get($code),
            );
        @endphp

        <div class="mb-4 pb-3 border-bottom">
            <strong>Informativa privacy</strong>
            <div class="text-muted small">
                Presa visione il {{ $privacyConsent?->granted_at?->format('d/m/Y') ?? '—' }}
            </div>
        </div>

        <form method="POST" action="{{ route('wn-plus.portal.profile.consents') }}">
            @csrf
            @method('PUT')

            <h3 class="h6 text-uppercase text-muted mb-3">Visibilità nella community</h3>

            <div class="form-check form-switch wn-consent-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch"
                       name="{{ $basicCode }}" value="1"
                       id="{{ $basicCode }}"
                       data-wn-visibility-master
                       {{ $isGranted($basicCode) ? 'checked' : '' }}>
                <label class="form-check-label" for="{{ $basicCode }}">
                    {{ $labelFor($basicCode) }}
                </label>
            </div>

            <div class="ps-4 mb-4">
                @foreach ($visibilityGroup as $code)
                    @php
                        // Foto è indipendente; email e telefono seguono il profilo base.
                        $dependsOnBasic = $code !== $photoCode;
                    @endphp

                    <div class="form-check form-switch wn-consent-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch"
                               name="{{ $code }}" value="1" id="{{ $code }}"
                               @if ($dependsOnBasic) data-wn-visibility-dependent @endif
                               {{ $isGranted($code) ? 'checked' : '' }}
                               @if ($dependsOnBasic && ! $isGranted($basicCode)) disabled @endif>
                        <label class="form-check-label" for="{{ $code }}">
                            {{ $labelFor($code) }}
                        </label>
                    </div>
                @endforeach
            </div>

            <h3 class="h6 text-uppercase text-muted mb-3">Altre scelte</h3>

            @foreach ($otherConsents as $code)
                <div class="form-check form-switch wn-consent-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch"
                           name="{{ $code }}" value="1" id="{{ $code }}"
                           {{ $isGranted($code) ? 'checked' : '' }}>
                    <label class="form-check-label" for="{{ $code }}">
                        {{ $labelFor($code) }}
                    </label>
                </div>
            @endforeach

            <button type="submit" class="wn-auth-submit mt-3">Salva consensi</button>
        </form>

        <p class="text-muted small mt-3 mb-0">
            Puoi revocare ogni consenso in qualsiasi momento. Per modificare i tuoi dati
            anagrafici o di contatto scrivi a
            <a href="mailto:plus@welfarenest.it">plus@welfarenest.it</a>.
        </p>

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
    </div>
</div>
@endsection