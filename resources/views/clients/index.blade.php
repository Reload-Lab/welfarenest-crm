@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Clienti</h2>
    <a href="/clients/create" class="btn btn-primary">Nuovo cliente</a>
</div>

<form method="GET" action="/clients" class="mb-3">

    <div class="row g-2">

        <div class="col">
            <input type="text"
                   name="search"
                   class="form-control"
                   placeholder="Cerca cliente..."
                   value="{{ $search }}">
        </div>

        <div class="col-auto">
            <button class="btn btn-primary">Cerca</button>
        </div>

    </div>

</form>


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
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clients as $client)
                    <tr>
                        <td>{{ $client->id }}</td>
                        <td>{{ $client->name }}</td>
                        <td>{{ $client->email }}</td>
                        <td>{{ $client->phone }}</td>
                        <td>
                            <a href="/clients/{{ $client->id }}/edit" class="btn btn-sm btn-warning">
                                Modifica
                            </a>

                            <form action="/clients/{{ $client->id }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Eliminare questo cliente?')">
                                    Elimina
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $clients->appends(['search' => $search])->links() }}
    
@else
    <div class="alert alert-info">Nessun cliente presente</div>
@endif

@endsection