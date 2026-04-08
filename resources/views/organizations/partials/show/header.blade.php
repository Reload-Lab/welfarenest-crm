<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <h2 class="h4 mb-0">
                        {{ $organization->name ?: '—' }}
                    </h2>

                    @if($organization->is_active)
                        <span class="badge text-bg-success">Attiva</span>
                    @else
                        <span class="badge text-bg-secondary">Non attiva</span>
                    @endif

                    @if($organization->organizationType)
                        <span class="badge text-bg-light border">
                            {{ $organization->organizationType->name }}
                        </span>
                    @endif
                </div>

                @if($organization->legal_name && $organization->legal_name !== $organization->name)
                    <div class="text-muted mb-2">
                        <strong>Denominazione legale:</strong> {{ $organization->legal_name }}
                    </div>
                @endif

                <div class="row g-3 mt-1">
                    <div class="col-12 col-md-auto">
                        <div class="small text-muted">ID</div>
                        <div class="fw-semibold">#{{ $organization->id }}</div>
                    </div>

                    <div class="col-12 col-md-auto">
                        <div class="small text-muted">Creata il</div>
                        <div class="fw-semibold">
                            {{ optional($organization->created_at)->format('d/m/Y H:i') ?? '—' }}
                        </div>
                    </div>

                    <div class="col-12 col-md-auto">
                        <div class="small text-muted">Ultimo aggiornamento</div>
                        <div class="fw-semibold">
                            {{ optional($organization->updated_at)->format('d/m/Y H:i') ?? '—' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-start gap-2">
                <a href="{{ route('organizations.edit', $organization) }}" class="btn btn-primary">
                    Modifica
                </a>

                <form action="{{ route('organizations.destroy', $organization) }}" method="POST" onsubmit="return confirm('Vuoi eliminare questa organizzazione?');">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-outline-danger">
                        Elimina
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>