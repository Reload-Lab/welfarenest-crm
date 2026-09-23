@extends('layouts.app')

@section('title', 'Cambia password')

@php
    // Fortify valida in un error bag dedicato (updatePassword) e, a salvataggio
    // riuscito, torna indietro con status 'password-updated'.
    $bag = $errors->getBag('updatePassword');
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 col-lg-6 col-xl-5">

            @if(session('status') === 'password-updated')
                <div class="alert alert-success">
                    Password aggiornata. Al prossimo accesso usa quella nuova.
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <h2 class="h6 mb-1">Cambia password</h2>
                    <p class="text-muted small mb-4">
                        Serve la password attuale. La nuova deve essere di almeno
                        8 caratteri e va ripetuta per conferma.
                    </p>

                    <form method="POST" action="{{ route('user-password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password attuale</label>
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="form-control @if($bag->has('current_password')) is-invalid @endif"
                                autocomplete="current-password"
                                required
                                autofocus
                            >
                            @if($bag->has('current_password'))
                                <div class="invalid-feedback">{{ $bag->first('current_password') }}</div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Nuova password</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control @if($bag->has('password')) is-invalid @endif"
                                autocomplete="new-password"
                                required
                            >
                            @if($bag->has('password'))
                                <div class="invalid-feedback">{{ $bag->first('password') }}</div>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Ripeti la nuova password</label>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="form-control"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                Annulla
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <x-icon group="actions" name="save" class="me-1" />
                                Salva password
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
