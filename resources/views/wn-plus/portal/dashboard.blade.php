@extends('layouts.guest')

@section('title', 'Area WN+')

@section('content')
<div class="container py-5">
    <div class="card mx-auto border-0 shadow-sm" style="max-width: 640px;">
        <div class="card-body p-4">
            <h1 class="h4 mb-3">Area Welfare Nest Plus</h1>

            <p class="text-muted">
                Accesso effettuato come:
            </p>

            <dl>
                <dt>Nome</dt>
                <dd>{{ $account->full_name }}</dd>

                <dt>Email</dt>
                <dd>{{ $account->email }}</dd>

                <dt>Organizzazione</dt>
                <dd>{{ $account->organization?->name ?? $account->organization?->legal_name }}</dd>

                <dt>Ruolo</dt>
                <dd>{{ $account->role?->name }}</dd>

                <dt>Livello</dt>
                <dd>{{ $account->level?->name }}</dd>
            </dl>

            <form method="POST" action="{{ route('wn-plus.logout') }}">
                @csrf

                <button type="submit" class="btn btn-outline-secondary">
                    Esci
                </button>
            </form>
        </div>
    </div>
</div>
@endsection