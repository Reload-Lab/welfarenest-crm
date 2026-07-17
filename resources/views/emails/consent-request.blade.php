<x-mail::message>
# Conferma dei consensi

Abbiamo registrato questo indirizzo email nei sistemi Welfare Nest.

Per consultare l’informativa e indicare le tue preferenze, utilizza il pulsante seguente.

<x-mail::button :url="$consentUrl">
Gestisci i consensi
</x-mail::button>

Il collegamento resterà valido fino al
{{ $consentRequest->expires_at->format('d/m/Y H:i') }}.

Se non riconosci questa richiesta, puoi ignorare il messaggio.

Grazie,<br>
Welfare Nest
</x-mail::message>