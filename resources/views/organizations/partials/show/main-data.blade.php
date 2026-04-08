<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
        <h3 class="h5 mb-0">Dati principali</h3>
    </div>

    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-12 col-md-6">
                <div class="small text-muted mb-1">Partita IVA</div>
                <div class="fw-semibold">{{ $organization->vat_number ?: '—' }}</div>
            </div>

            <div class="col-12 col-md-6">
                <div class="small text-muted mb-1">Codice fiscale</div>
                <div class="fw-semibold">{{ $organization->tax_code ?: '—' }}</div>
            </div>

            <div class="col-12 col-md-6">
                <div class="small text-muted mb-1">Codice SDI</div>
                <div class="fw-semibold">{{ $organization->sdi_code ?: '—' }}</div>
            </div>

            <div class="col-12 col-md-6">
                <div class="small text-muted mb-1">Split payment</div>
                <div>
                    @if(!is_null($organization->is_split_payment))
                        @if($organization->is_split_payment)
                            <span class="badge text-bg-warning">Sì</span>
                        @else
                            <span class="badge text-bg-light border text-dark">No</span>
                        @endif
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>