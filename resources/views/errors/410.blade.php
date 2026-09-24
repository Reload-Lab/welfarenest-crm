{{--
  Link di raccolta consensi scaduto o già usato.
  Unico punto dell'applicazione che risponde 410 (ConsentRequestController::show()
  e ::complete()), quindi la pagina può parlare direttamente di quel caso.
--}}
@extends('layouts.consent-public')

@section('title', 'Link non più valido')

@section('card-class', 'is-centered')

@section('content')
    <h1>Link non più valido</h1>

    <p class="lead">
        Il collegamento che hai aperto è scaduto oppure è già stato utilizzato,
        quindi non è più possibile registrare le tue scelte da qui.
    </p>

    <p class="note">
        Se ti serve un nuovo link, rispondi all’email che hai ricevuto
        o contatta il tuo referente Welfare Nest.
    </p>
@endsection
