<x-mail::message>
# Benvenuto in Welfare Nest Plus

Ciao {{ $invitation->account->full_name }},

è stato creato per te un account per accedere a **Welfare Nest Plus**.

**Email:** {{ $invitation->account->email }}

@if($invitation->account->organization)
**Organizzazione:** {{ $invitation->account->organization->name ?? $invitation->account->organization->legal_name }}
@endif

Per attivare il tuo account, imposta una password e conferma i consensi richiesti.

<x-mail::button :url="$activationUrl">
Attiva account
</x-mail::button>

Il link è valido fino al {{ $invitation->expires_at->format('d/m/Y H:i') }}.

Grazie,<br>
{{ config('mail.from.name') }}
</x-mail::message>