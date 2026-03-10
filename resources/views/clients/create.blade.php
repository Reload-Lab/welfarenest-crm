@extends('layouts.app')

@section('content')

<h2>Nuovo cliente</h2>

<form method="POST" action="/clients">

    @csrf

    <div>
        <label>Nome</label><br>
        <input type="text" name="name">
    </div>

    <div>
        <label>Email</label><br>
        <input type="text" name="email">
    </div>

    <div>
        <label>Telefono</label><br>
        <input type="text" name="phone">
    </div>

    <br>

    <button type="submit">Salva</button>

</form>

@endsection