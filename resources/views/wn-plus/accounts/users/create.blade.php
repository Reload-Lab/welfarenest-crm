@extends('layouts.app')

@section('title', 'Nuovo utente WN+')

@section('content')
    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Nuovo utente WN+</h1>
            <div class="text-muted">
                Referente: {{ $account->full_name }} — {{ $account->organization?->name ?? $account->organization?->legal_name }}
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('wn-plus.accounts.users.store', $account) }}">
        @csrf

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="alert alert-light border mb-4">
                    Slot disponibili:
                    <strong>{{ $account->available_slots }}</strong>
                    su
                    <strong>{{ $account->max_users }}</strong>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nome</label>
                        <input
                            type="text"
                            name="first_name"
                            class="form-control"
                            value="{{ old('first_name') }}"
                            required
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Cognome</label>
                        <input
                            type="text"
                            name="last_name"
                            class="form-control"
                            value="{{ old('last_name') }}"
                            required
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="{{ old('email') }}"
                            required
                        >
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="{{ route('wn-plus.accounts.show', $account) }}" class="btn btn-outline-secondary">
                    Annulla
                </a>

                <button type="submit" class="btn btn-primary">
                    Salva utente
                </button>
            </div>
        </div>
    </form>
@endsection