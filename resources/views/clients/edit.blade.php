@extends('layouts.app')

@section('content')

<h2 class="h4 mb-4">Modifica cliente</h2>

<form method="POST" action="/clients/{{ $client->id }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label class="form-label">Nome</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $client->name) }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="text" name="email" class="form-control" value="{{ old('email', $client->email) }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Telefono</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $client->phone) }}">
    </div>

    <button type="submit" class="btn btn-success">Aggiorna</button>
    <a href="/clients" class="btn btn-secondary">Annulla</a>

</form>

@endsection