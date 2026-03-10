@extends('layouts.app')

@section('content')

<h2>Clienti</h2>

<p><a href="/clients/create">Nuovo cliente</a></p>

@if($clients->count())
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Telefono</th>
        </tr>

        @foreach($clients as $client)
            <tr>
                <td>{{ $client->id }}</td>
                <td>{{ $client->name }}</td>
                <td>{{ $client->email }}</td>
                <td>{{ $client->phone }}</td>
            </tr>
        @endforeach
    </table>

    <br>

    {{ $clients->links() }}
@else
    <p>Nessun cliente presente</p>
@endif

@endsection