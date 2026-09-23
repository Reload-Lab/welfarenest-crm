{{--
    Filtri comuni ai tre registri: chi ha agito e in quale periodo.
    $users e $filters arrivano dal LogController.
--}}
<div class="col-12 col-md-4 col-lg-3">
    <label for="user_id" class="form-label fw-semibold">Autore</label>
    <select name="user_id" id="user_id" class="form-select">
        <option value="">Tutti</option>
        <option value="none" {{ $filters['user_id'] === 'none' ? 'selected' : '' }}>
            Nessun utente (automatico)
        </option>
        @foreach($users as $user)
            <option value="{{ $user->id }}" {{ (string) $filters['user_id'] === (string) $user->id ? 'selected' : '' }}>
                {{ $user->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-6 col-md-4 col-lg-2">
    <label for="from" class="form-label fw-semibold">Dal</label>
    <input type="date" name="from" id="from" value="{{ $filters['from'] }}" class="form-control">
</div>

<div class="col-6 col-md-4 col-lg-2">
    <label for="to" class="form-label fw-semibold">Al</label>
    <input type="date" name="to" id="to" value="{{ $filters['to'] }}" class="form-control">
</div>
