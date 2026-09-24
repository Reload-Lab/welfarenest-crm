@extends('layouts.consent-public')

@section('title', 'Gestisci i tuoi consensi')

@section('content')
    <h1>Gestisci i tuoi consensi</h1>

    <p class="recipient">
        Preferenze per <strong>{{ $consentRequest->contactPoint?->value ?? 'recapito non disponibile' }}</strong>
    </p>

    @if ($errors->any())
        <p class="alert">{{ $errors->first() }}</p>
    @endif

    <form id="consent-form" method="POST" action="{{ route('consent-requests.complete', $consentRequest->token) }}">
        @csrf

        @if ($requiredItems->isNotEmpty())
            <fieldset>
                <legend>Informativa privacy <span class="req">richiesta per proseguire</span></legend>
                @foreach ($requiredItems as $item)
                    @include('consent-requests.partials.item', ['item' => $item])
                @endforeach
            </fieldset>
        @endif

        @if ($optionalItems->isNotEmpty())
            <fieldset>
                <legend>Consensi facoltativi</legend>
                @foreach ($optionalItems as $item)
                    @include('consent-requests.partials.item', ['item' => $item])
                @endforeach
            </fieldset>
        @endif

        <button type="submit" class="submit">Conferma le mie scelte</button>
        <p class="note">Il link è valido fino al {{ $consentRequest->expires_at->format('d/m/Y H:i') }}.</p>
    </form>
@endsection

@push('scripts')
@verbatim
<script>
(function(){
  var form = document.getElementById('consent-form');
  if (!form) return;

  var required = Array.prototype.slice.call(
    form.querySelectorAll('input[type="checkbox"][required]')
  );
  var btn = form.querySelector('.submit');

  // Validazione lato client: sostituisce i tooltip nativi del browser con
  // l'errore inline previsto dal design. Il controllo vero resta comunque
  // server-side in ConsentRequestController::complete().
  form.noValidate = true;

  function box(el){ return el.closest('.option'); }
  function msg(el){ return box(el).querySelector('.error'); }

  function clear(el){
    box(el).classList.remove('is-invalid');
    el.removeAttribute('aria-invalid');
    var m = msg(el);
    if (m) m.textContent = '';
  }

  required.forEach(function(el){
    el.addEventListener('change', function(){ if (el.checked) clear(el); });
  });

  form.addEventListener('submit', function(event){
    var first = null;

    required.forEach(function(el){
      if (el.checked) { clear(el); return; }
      box(el).classList.add('is-invalid');
      el.setAttribute('aria-invalid', 'true');
      var m = msg(el);
      if (m) m.textContent = 'Per proseguire conferma di aver letto l’informativa.';
      if (!first) first = el;
    });

    if (first) {
      event.preventDefault();
      first.focus();
      return;
    }

    btn.setAttribute('aria-busy', 'true');
    btn.textContent = 'Salvataggio in corso…';
  });
})();
</script>
@endverbatim
@endpush
