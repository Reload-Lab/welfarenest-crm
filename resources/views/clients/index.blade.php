@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Clienti</h2>
    <a href="/clients/create" class="btn btn-primary">Nuovo cliente</a>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif


@if($clients->count())
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Telefono</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clients as $client)
                    <tr>
                        <td>{{ $client->id }}</td>
                        <td>{{ $client->name }}</td>
                        <td>{{ $client->email }}</td>
                        <td>{{ $client->phone }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $clients->links() }}
@else
    <div class="alert alert-info">Nessun cliente presente</div>
@endif

@endsection