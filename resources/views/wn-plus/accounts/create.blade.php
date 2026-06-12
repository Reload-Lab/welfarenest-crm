@extends('layouts.app')

@section('title', 'Nuovo Utente WN+')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Nuovo Utente WN+</h1>
        <p class="text-muted mb-0">
            Crea un nuovo account per Welfare Nest Plus.
        </p>
    </div>
</div>

<form method="POST" action="{{ route('wn-plus.accounts.store') }}">
    @csrf

    @include('wn-plus.accounts._form')

</form>

@endsection