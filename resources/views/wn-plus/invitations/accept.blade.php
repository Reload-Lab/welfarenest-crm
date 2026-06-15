@extends('layouts.guest')

@section('title', 'Attivazione account WN+')

@section('content')

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <h1 class="h3 mb-3">
                        Attiva il tuo account WN+
                    </h1>

                    <p class="text-muted">
                        Ciao {{ $invitation->account->full_name }},
                        imposta una password e conferma i consensi per accedere a Welfare Nest Plus.
                    </p>

                    <dl class="small mb-4">
                        <dt>Email</dt>
                        <dd>{{ $invitation->account->email }}</dd>

                        <dt>Organizzazione</dt>
                        <dd>
                            {{ $invitation->account->organization?->name
                                ?? $invitation->account->organization?->legal_name }}
                        </dd>
                    </dl>

                    <form method="POST" action="{{ route('wn-plus.invitations.complete', $invitation->token) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input
                                type="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                required
                                minlength="8"
                            >
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Conferma password</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                class="form-control"
                                required
                                minlength="8"
                            >
                        </div>

                        <div class="border rounded p-3 mb-3">
                            <div class="form-check">
                                <input
                                    class="form-check-input @error('privacy_base') is-invalid @enderror"
                                    type="checkbox"
                                    name="privacy_base"
                                    value="1"
                                    id="privacy_base"
                                    required
                                >
                                <label class="form-check-label" for="privacy_base">
                                    Dichiaro di aver letto l’informativa privacy.
                                </label>

                                @error('privacy_base')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="border rounded p-3 mb-3">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="image_disclosure"
                                    value="1"
                                    id="image_disclosure"
                                >
                                <label class="form-check-label" for="image_disclosure">
                                    Acconsento alla pubblicazione di immagini e contenuti multimediali.
                                </label>
                            </div>
                        </div>

                        <div class="border rounded p-3 mb-4">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="promotional_emails"
                                    value="1"
                                    id="promotional_emails"
                                >
                                <label class="form-check-label" for="promotional_emails">
                                    Acconsento a ricevere comunicazioni informative e promozionali.
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Attiva account
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

@endsection