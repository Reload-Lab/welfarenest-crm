@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Organizzazioni</h2>
    <a href="{{ route('organizations.create') }}" class="btn btn-primary">Nuova organizzazione</a>
</div>

<form method="GET" action="{{ route('organizations.index') }}" class="mb-3">
    <div class="row g-2">
        <div class="col">
            <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Cerca organizzazione..."
                value="{{ $search }}"
            >
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

@if($organizations->count())
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
                        <a href="?search={{ urlencode($search) }}&sort=legal_name&direction={{ nextDirection('legal_name', $sort, $direction) }}">
                            Ragione sociale{!! sortIcon('legal_name', $sort, $direction) !!}
                        </a>
                    </th>

                    <th>
                        <a href="?search={{ urlencode($search) }}&sort=vat_number&direction={{ nextDirection('vat_number', $sort, $direction) }}">
                            P. IVA{!! sortIcon('vat_number', $sort, $direction) !!}
                        </a>
                    </th>

                    <th>
                        <a href="?search={{ urlencode($search) }}&sort=tax_code&direction={{ nextDirection('tax_code', $sort, $direction) }}">
                            Codice fiscale{!! sortIcon('tax_code', $sort, $direction) !!}
                        </a>
                    </th>

                    <th>
                        <a href="?search={{ urlencode($search) }}&sort=is_active&direction={{ nextDirection('is_active', $sort, $direction) }}">
                            Stato{!! sortIcon('is_active', $sort, $direction) !!}
                        </a>
                    </th>

                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                @foreach($organizations as $organization)
                    <tr>
                        <td>{{ $organization->id }}</td>
                        <td>{{ $organization->name ?: '—' }}</td>
                        <td>{{ $organization->legal_name ?: '—' }}</td>
                        <td>{{ $organization->vat_number ?: '—' }}</td>
                        <td>{{ $organization->tax_code ?: '—' }}</td>
                        <td>
                            @if($organization->is_active)
                                <span class="badge text-bg-success">Attiva</span>
                            @else
                                <span class="badge text-bg-secondary">Non attiva</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('organizations.edit', $organization) }}" class="btn btn-sm btn-warning">
                                Modifica
                            </a>

                            <form action="{{ route('organizations.destroy', $organization) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Eliminare questa organizzazione?')"
                                >
                                    Elimina
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $organizations->appends(['search' => $search, 'sort' => $sort, 'direction' => $direction])->links() }}
@else
    <div class="alert alert-info">Nessuna organizzazione presente</div>
@endif

@endsection