@extends('layouts.app')

@section('content')

<h2>Clienti</h2>

@if($clients->count())
    <ul>
        @foreach($clients as $client)
            <li>
                {{ $client->name }} - {{ $client->email }} - {{ $client->phone }}
            </li>
        @endforeach
    </ul>
@else
    <p>Nessun cliente presente</p>
@endif

@endsection