@extends('layouts.app')

@section('topbar_title', 'Persone')
@section('topbar_subtitle', 'Gestione anagrafiche persone')

@section('pageHeader')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <h1 class="crm-page-title">Persone</h1>
            <p class="crm-page-subtitle mb-0">
                Elenco e gestione delle anagrafiche personali
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('people.create') }}" class="btn btn-primary">
                Nuova persona
            </a>
        </div>
    </div>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('people.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-7">
                        <label for="search" class="form-label fw-semibold">Ricerca</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            class="form-control"
                            placeholder="Nome o cognome"
                            value="{{ $search }}"
                        >
                    </div>

                    <div class="col-12 col-md-4 col-lg-2">
                        <label for="per_page" class="form-label fw-semibold">Righe</label>
                        <select name="per_page" id="per_page" class="form-select">
                            <option value="10" {{ (int) $perPage === 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ (int) $perPage === 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ (int) $perPage === 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-8 col-lg-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                Filtra
                            </button>

                            <a href="{{ route('people.index') }}" class="btn btn-outline-secondary">
                                Reset
                            </a>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div>
                    <h2 class="h5 mb-1">Anagrafiche persone</h2>
                    <p class="text-muted mb-0 small">
                        {{ $people->total() }} risultati trovati
                    </p>
                </div>
            </div>
        </div>

        <div class="card-body p-0 pt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Nome</th>
                            <th>Cognome</th>
                            <th>Relazioni</th>
                            <th class="text-end pe-4">Azioni</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($people as $person)
                            <tr>
                                <td class="ps-4">{{ $person->first_name ?: '—' }}</td>
                                <td>
                                    <a href="{{ route('people.show', $person) }}" class="text-decoration-none fw-semibold">
                                        {{ $person->last_name ?: '—' }}
                                    </a>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        {{ $person->organization_relations_count ?? '—' }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('people.show', $person) }}" class="btn btn-sm btn-outline-secondary">
                                            Apri
                                        </a>
                                        <a href="{{ route('people.edit', $person) }}" class="btn btn-sm btn-outline-primary">
                                            Modifica
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="text-muted mb-2">Nessuna persona trovata</div>
                                    <a href="{{ route('people.create') }}" class="btn btn-primary btn-sm">
                                        Crea la prima persona
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($people->hasPages())
            <div class="card-footer bg-white border-0 px-4 py-3">
                {{ $people->links() }}
            </div>
        @endif
    </div>
@endsection
