@extends('layouts.app')

@section('title', 'Modifica account WN+')

@section('content')
    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Modifica account WN+</h1>
            <div class="text-muted">
                {{ $account->full_name }} — {{ $account->organization?->name ?? $account->organization?->legal_name }}
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('wn-plus.accounts.update', $account) }}">
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Email accesso WN+</label>
                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="{{ old('email', $account->email) }}"
                            required
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Ruolo</label>
                        <select name="wn_plus_role_id" class="form-select" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" @selected(old('wn_plus_role_id', $account->wn_plus_role_id) == $role->id)>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Livello</label>
                        <select name="wn_plus_level_id" class="form-select" required>
                            @foreach($levels as $level)
                                <option value="{{ $level->id }}" @selected(old('wn_plus_level_id', $account->wn_plus_level_id) == $level->id)>
                                    {{ $level->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Stato</label>
                        <select name="status" class="form-select" required>
                            @foreach(['invited' => 'Invitato', 'active' => 'Attivo', 'suspended' => 'Sospeso', 'disabled' => 'Disabilitato'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $account->status) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Numero massimo utenti</label>
                        <input
                            type="number"
                            name="max_users"
                            class="form-control"
                            value="{{ old('max_users', $account->max_users) }}"
                            min="0"
                        >
                    </div>

                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="{{ route('wn-plus.accounts.show', $account) }}" class="btn btn-outline-secondary">
                    Annulla
                </a>

                <button type="submit" class="btn btn-primary">
                    Salva modifiche
                </button>
            </div>
        </div>
    </form>
@endsection