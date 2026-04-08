@extends('layouts.app')

@section('topbar_title', 'Clienti')
@section('topbar_subtitle', 'Gestione anagrafiche clienti')

@section('pageHeader')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <h1 class="crm-page-title">Clienti</h1>
            <p class="crm-page-subtitle mb-0">
                Elenco, ricerca e gestione delle organizzazioni registrate
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('organizations.create') }}" class="btn btn-primary">
                Nuovo cliente
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
            <form method="GET" action="{{ route('organizations.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-5">
                        <label for="search" class="form-label fw-semibold">Ricerca</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            class="form-control"
                            placeholder="Nome, ragione sociale, P.IVA, codice fiscale"
                            value="{{ $search }}"
                        >
                    </div>

                    <div class="col-12 col-md-4 col-lg-2">
                        <label for="status" class="form-label fw-semibold">Stato</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Tutti</option>
                            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Attivi</option>
                            <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Non attivi</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4 col-lg-2">
                        <label for="per_page" class="form-label fw-semibold">Righe</label>
                        <select name="per_page" id="per_page" class="form-select">
                            <option value="10" {{ (int) $perPage === 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ (int) $perPage === 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ (int) $perPage === 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4 col-lg-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                Filtra
                            </button>

                            <a href="{{ route('organizations.index') }}" class="btn btn-outline-secondary">
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
                    <h2 class="h5 mb-1">Anagrafiche clienti</h2>
                    <p class="text-muted mb-0 small">
                        {{ $organizations->total() }} risultati trovati
                    </p>
                </div>
            </div>
        </div>

        <div class="card-body p-0 pt-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">
                                @include('organizations.partials.sortable-th', ['label' => 'Nome', 'field' => 'name'])
                            </th>
                            <th>
                                @include('organizations.partials.sortable-th', ['label' => 'Ragione sociale', 'field' => 'legal_name'])
                            </th>
                            <th>
                                @include('organizations.partials.sortable-th', ['label' => 'P.IVA', 'field' => 'vat_number'])
                            </th>
                            <th>
                                @include('organizations.partials.sortable-th', ['label' => 'Codice fiscale', 'field' => 'tax_code'])
                            </th>
                            <th>
                                @include('organizations.partials.sortable-th', ['label' => 'Stato', 'field' => 'is_active'])
                            </th>
                            <th class="text-end pe-4">Azioni</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($organizations as $organization)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold text-dark">
                                        <a href="{{ route('organizations.show', $organization) }}" class="text-decoration-none fw-semibold">
                                            {{ $organization->name ?: $organization->legal_name }}
                                        </a>
                                    </div>
                                </td>

                                <td>
                                    <div class="text-dark">
                                        {{ $organization->legal_name ?: '—' }}
                                    </div>
                                </td>

                                <td>
                                    <span class="text-muted">
                                        {{ $organization->vat_number ?: '—' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="text-muted">
                                        {{ $organization->tax_code ?: '—' }}
                                    </span>
                                </td>

                                <td>
                                    @if($organization->is_active)
                                        <span class="badge rounded-pill text-bg-success">
                                            Attivo
                                        </span>
                                    @else
                                        <span class="badge rounded-pill text-bg-secondary">
                                            Non attivo
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('organizations.edit', $organization) }}" class="btn btn-sm btn-outline-primary">
                                            Modifica
                                        </a>

                                        <form
                                            action="{{ route('organizations.destroy', $organization) }}"
                                            method="POST"
                                            onsubmit="return confirm('Confermi l\'eliminazione di questo cliente?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                Elimina
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted mb-2">Nessun cliente trovato</div>
                                    <a href="{{ route('organizations.create') }}" class="btn btn-primary btn-sm">
                                        Crea il primo cliente
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($organizations->hasPages())
            <div class="card-footer bg-white border-0 px-4 py-3">
                {{ $organizations->links() }}
            </div>
        @endif
    </div>
@endsection