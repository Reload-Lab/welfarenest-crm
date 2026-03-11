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

@php
    function sortIcon($column, $sort, $direction) {
        if ($sort !== $column) {
            return '';
        }

        return $direction === 'asc' ? ' ↑' : ' ↓';
    }

    function nextDirection($column, $sort, $direction) {
        if ($sort === $column) {
            return $direction === 'asc' ? 'desc' : 'asc';
        }

        return 'asc';
    }
@endphp

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
                    <th>
                        <a href="?search={{ urlencode($search) }}&sort=id&direction={{ nextDirection('id', $sort, $direction) }}">
                            ID{!! sortIcon('id', $sort, $direction) !!}
                        </a>
                    </th>

                    <th>
                        <a href="?search={{ urlencode($search) }}&sort=name&direction={{ nextDirection('name', $sort, $direction) }}">
                            Nome{!! sortIcon('name', $sort, $direction) !!}
                        </a>
                    </th>

                    <th>
                        <a href="?search={{ urlencode($search) }}&sort=email&direction={{ nextDirection('email', $sort, $direction) }}">
                            Email{!! sortIcon('email', $sort, $direction) !!}
                        </a>
                    </th>

                    <th>
                        <a href="?search={{ urlencode($search) }}&sort=phone&direction={{ nextDirection('phone', $sort, $direction) }}">
                            Telefono{!! sortIcon('phone', $sort, $direction) !!}
                        </a>
                    </th>
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