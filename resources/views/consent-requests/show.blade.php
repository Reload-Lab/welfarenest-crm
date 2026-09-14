@extends('layouts.guest')

@section('title', 'Gestione consensi')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h1 class="h4 mb-3">Gestione dei tuoi consensi</h1>

            <p class="text-muted">
                Recapito: <strong>{{ $consentRequest->contactPoint?->value ?? 'Recapito non disponibile' }}</strong>
            </p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('consent-requests.complete', $consentRequest->token) }}">
                @csrf

                @foreach ($consentRequest->items as $item)
                    <div class="border rounded p-3 mb-3">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="consent_{{ $item->consent_type_id }}"
                                id="consent_{{ $item->consent_type_id }}"
                                value="1"
                                {{ $item->is_required ? 'required' : '' }}
                            >
                            <label class="form-check-label" for="consent_{{ $item->consent_type_id }}">
                                {{ $item->consentVersion?->title ?? $item->consentType->name }}
                                @if ($item->is_required)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                        </div>

                        @if ($item->consentVersion?->content_file_path)
                            <a
                                href="{{ route('consent-requests.document', ['token' => $consentRequest->token, 'consentVersionId' => $item->consent_version_id]) }}"
                                target="_blank"
                                rel="noopener"
                                class="small"
                            >
                                Leggi l'informativa completa
                            </a>
                        @endif
                    </div>
                @endforeach

                <p class="small text-muted">
                    I campi contrassegnati con <span class="text-danger">*</span> sono obbligatori per proseguire.
                </p>

                <button type="submit" class="btn btn-primary w-100">Conferma le mie scelte</button>
            </form>

            <p class="small text-muted mt-3 mb-0">
                Il collegamento è valido fino al {{ $consentRequest->expires_at->format('d/m/Y H:i') }}.
            </p>
        </div>
    </div>
@endsection
