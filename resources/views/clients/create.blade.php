@extends('layouts.app')

@section('content')

<h2 class="h4 mb-4">Nuovo cliente</h2>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="/clients">
    @csrf

    <div class="mb-3">
        <label class="form-label">Nome</label>
        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="text" name="email" class="form-control" value="{{ old('email') }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Telefono</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
    </div>

    <button type="submit" class="btn btn-success">Salva</button>
    <a href="/clients" class="btn btn-secondary">Annulla</a>
</form>

@endsection