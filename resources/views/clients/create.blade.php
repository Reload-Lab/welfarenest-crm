@extends('layouts.app')

@section('content')

<h2 class="h4 mb-4">Nuovo cliente</h2>

<form method="POST" action="/clients">
    @csrf

    <div class="mb-3">
        <label class="form-label">Nome</label>
        <input type="text" name="name" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="text" name="email" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Telefono</label>
        <input type="text" name="phone" class="form-control">
    </div>

    <button type="submit" class="btn btn-success">Salva</button>
    <a href="/clients" class="btn btn-secondary">Annulla</a>
</form>

@endsection