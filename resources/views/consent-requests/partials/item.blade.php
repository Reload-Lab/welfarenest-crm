{{--
  Singola voce di consenso nel form pubblico.
  Attesa: $item (ConsentRequestItem con consentType/consentVersion caricati),
  $consentRequest.
--}}
@php
    $inputId = 'consent_'.$item->consent_type_id;
    $errorId = 'err_'.$item->consent_type_id;
    $statement = config('consent_statements.'.$item->consentType->code)
        ?? $item->consentVersion?->title
        ?? $item->consentType->name;
@endphp

<div class="option">
    <label class="check" for="{{ $inputId }}">
        <input
            type="checkbox"
            id="{{ $inputId }}"
            name="{{ $inputId }}"
            value="1"
            @checked(old($inputId))
            @if ($item->is_required) required aria-describedby="{{ $errorId }}" @endif
        >
        <span>{{ $statement }}</span>
    </label>

    @if ($item->consentVersion?->content_file_path)
        <a
            class="doc"
            href="{{ route('consent-requests.document', ['token' => $consentRequest->token, 'consentVersionId' => $item->consent_version_id]) }}"
            target="_blank"
            rel="noopener"
        >Leggi l’informativa</a>
    @endif

    @if ($item->is_required)
        <p class="error" id="{{ $errorId }}" aria-live="polite"></p>
    @endif
</div>
