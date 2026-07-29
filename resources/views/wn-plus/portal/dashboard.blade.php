@extends('layouts.wn-plus')

@section('title', 'Area WN+')
@section('body_class', 'wn-auth-page')

@section('content')
<div class="container py-5">
    @include('wn-plus.portal.partials.nav', ['active' => 'dashboard'])

    <div class="wn-auth-card mx-auto p-4 p-md-5" style="max-width: 640px;">
        <h1 class="wn-auth-title mb-4">Benvenuto, {{ $account->first_name }}</h1>

        <dl class="row mb-0">
            <dt class="col-sm-4">Organizzazione</dt>
            <dd class="col-sm-8">{{ $account->organization?->name ?? $account->organization?->legal_name }}</dd>

            <dt class="col-sm-4">Ruolo</dt>
            <dd class="col-sm-8">{{ $account->role?->name }}</dd>

            <dt class="col-sm-4">Livello</dt>
            <dd class="col-sm-8">{{ $account->level?->name }}</dd>
        </dl>
    </div>
</div>
@endsection