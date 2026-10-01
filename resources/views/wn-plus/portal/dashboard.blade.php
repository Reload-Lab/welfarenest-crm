@extends('layouts.wn-plus')

@section('title', 'Area riservata WN+')
@section('body_class', 'wn-auth-page')
@section('full_page', 'true')

@section('content')
@php
    $siteUrl = trim((string) config('services.wn_plus_site.area_url'));

    // Il testo si compone qui e non dentro il markup: una direttiva Blade attaccata
    // alla parola precedente (consensi@if) non viene compilata e finisce stampata
    // a schermo, perché Blade non riconosce la chiocciola dopo un carattere di parola.
    $intro = 'Il tuo account Welfare Nest Plus è attivo. Da qui gestisci la tua password e le tue scelte sui consensi';

    $intro .= $account->account_type === 'manager'
        ? ', e inviti i componenti della tua organizzazione.'
        : '.';
@endphp

<div class="container py-5">
    @include('wn-plus.portal.partials.header')
    @include('wn-plus.portal.partials.nav', ['active' => 'dashboard'])

    @if(session('success'))
        <div class="alert alert-success mx-auto" style="max-width: 860px;">{{ session('success') }}</div>
    @endif

    <div class="wn-auth-card mx-auto p-4 p-md-5 mb-4">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-md-4 text-center">
                <div class="wn-auth-illustration mx-auto">
                    <svg width="96" height="96" viewBox="0 0 120 120" fill="none" aria-hidden="true">
                        <circle cx="60" cy="42" r="22" stroke="currentColor" stroke-width="5"/>
                        <path d="M18 104C18 83 36.8 70 60 70C83.2 70 102 83 102 104" stroke="currentColor" stroke-width="5" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>

            <div class="col-md-8">
                <h1 class="wn-auth-title mb-3">
                    Ciao <strong>{{ $account->first_name }}</strong>
                </h1>

                <p class="wn-auth-text mb-0">{{ $intro }}</p>
            </div>
        </div>

        <div class="wn-auth-info row g-4 py-4 mt-4">
            <div class="col-md-4">
                <div class="text-muted small text-uppercase">Organizzazione</div>
                <div class="fw-semibold">{{ $account->organization?->name ?? $account->organization?->legal_name ?? '—' }}</div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small text-uppercase">Ruolo</div>
                <div class="fw-semibold">{{ $account->role?->name ?? '—' }}</div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small text-uppercase">Livello</div>
                <div class="fw-semibold">{{ $account->level?->name ?? '—' }}</div>
            </div>
        </div>
    </div>

    @if ($siteUrl !== '')
        <div class="wn-portal-cta mx-auto p-4 p-md-5 text-center" style="max-width: 860px;">
            <h2 class="wn-portal-card-title mb-2">Entra nella community</h2>
            <p class="wn-auth-text mb-4">
                Articoli, laboratori di innovazione, eventi e la rivista semestrale ti
                aspettano nell’area riservata di Welfare Nest Plus.
            </p>
            <a href="{{ $siteUrl }}" class="wn-auth-submit">Vai a Welfare Nest Plus</a>
        </div>
    @endif

    @include('wn-plus.portal.partials.footer')
</div>
@endsection
