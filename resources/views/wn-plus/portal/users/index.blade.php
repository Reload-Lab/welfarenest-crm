@extends('layouts.wn-plus')

@section('title', 'I miei utenti')
@section('body_class', 'wn-auth-page')
@section('full_page', 'true')

@section('content')
<div class="container py-5">
    @include('wn-plus.portal.partials.header')
    @include('wn-plus.portal.partials.nav', ['active' => 'users'])

    @if(session('success'))
        <div class="alert alert-success mx-auto" style="max-width: 860px;">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mx-auto" style="max-width: 860px;">{{ session('error') }}</div>
    @endif

    <div class="wn-auth-card mx-auto p-4 p-md-5 mb-4" style="max-width: 860px;">
        <h2 class="h5 mb-1">Utenti di {{ $account->organization?->name ?? $account->organization?->legal_name }}</h2>
        <p class="text-muted small mb-4">
            Le persone della tua organizzazione che hai invitato in Welfare Nest Plus.
        </p>

        @forelse ($users as $user)
            <div class="d-flex justify-content-between align-items-center gap-3 py-3 {{ ! $loop->last ? 'border-bottom' : '' }}">
                <div>
                    <div class="fw-semibold">{{ $user->full_name }}</div>
                    <div class="text-muted small">{{ $user->email }}</div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted small">{{ $user->statusLabel() }}</span>

                    @if ($user->status !== 'active')
                        <form method="POST" action="{{ route('wn-plus.portal.users.resend', $user) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm">Reinvia invito</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-muted mb-0">Non hai ancora invitato nessuno.</p>
        @endforelse
    </div>

    <div class="wn-auth-card mx-auto p-4 p-md-5" style="max-width: 860px;">
        <h2 class="h5 mb-1">Invita una persona</h2>
        <p class="text-muted small mb-4">
            Riceverà un’email con l’informativa privacy e il link per attivare il proprio
            account, dove sceglierà la password e i propri consensi. Fino a quel momento
            non sarà visibile a nessuno.
        </p>

        <form method="POST" action="{{ route('wn-plus.portal.users.store') }}">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label class="form-label fw-semibold" for="first_name">Nome</label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}"
                           class="form-control wn-auth-input @error('first_name') is-invalid @enderror" required>
                    @error('first_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-sm-6">
                    <label class="form-label fw-semibold" for="last_name">Cognome</label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}"
                           class="form-control wn-auth-input @error('last_name') is-invalid @enderror" required>
                    @error('last_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold" for="email">Email professionale</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                       class="form-control wn-auth-input @error('email') is-invalid @enderror" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="wn-auth-submit">Invia invito</button>
        </form>
    </div>
    @include('wn-plus.portal.partials.footer')
</div>
@endsection
