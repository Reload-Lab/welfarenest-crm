@extends('layouts.app')

@section('topbar_title', 'Clienti')
@section('topbar_subtitle', 'Modifica anagrafica cliente')

@section('pageHeader')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <h1 class="crm-page-title">Modifica cliente</h1>
        </div>
    </div>
@endsection

@section('content')
    {{--
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">
            <div class="fw-semibold mb-1">Controlla i dati inseriti</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    --}}

    <form method="POST" action="{{ route('organizations.update', $organization) }}">
        @csrf
        @method('PUT')

        @include('organizations.partials.form', [
            'submitLabel' => 'Salva modifiche'
        ])
    </form>
@endsection