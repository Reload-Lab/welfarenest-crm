@extends('layouts.wn-plus')

@section('content')
<div class="wn-plus-auth-box text-center">
    <h1 class="h4 mb-3">Disconnessione avvenuta</h1>
    <p class="text-muted mb-4">
        @if ($returnTo)
            Verrai reindirizzato tra qualche istante.
        @else
            La tua sessione su Welfare Nest Plus è stata chiusa.
        @endif
    </p>

    @if ($returnTo)
        <a href="{{ $returnTo }}" class="btn btn-primary">
            Continua ora
        </a>
    @else
        <a href="{{ route('wn-plus.login') }}" class="btn btn-primary">
            Torna al login
        </a>
    @endif
</div>
@endsection

@if ($returnTo)
    @push('scripts')
    <script>
        setTimeout(function () {
            window.location.href = @json($returnTo);
        }, 2500);
    </script>
    @endpush
@endif