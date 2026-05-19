<div class="card h-100 shadow-sm">
    <div class="card-header">
        <div>
            <h5>Dati principali</h5>
        </div>

        
    </div>

    <div class="card-body p-4">
        <div class="row g-4">

            <div class="col-12 col-md-6">
                <div class="small text-muted mb-1">Stato</div>
                <div class="fw-semibold">
                    @if($organization->is_active)
                        <span class="badge text-bg-success">Attiva</span>
                    @else
                        <span class="badge text-bg-secondary">Non attiva</span>
                    @endif
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="small text-muted mb-1">Tipo organizzazione</div>
                <div class="fw-semibold">
                    @if($organization->organizationType)
                        <x-crm.tag
                            :label="$organization->organizationType?->name"
                            icon-group="entities"
                            icon-name="organization"
                            variant="primary"
                        />
                    @endif
                </div>
            </div>







            <div class="col-12 col-md-6">
                <div class="small text-muted mb-1">Ragione sociale</div>
                <div class="fw-semibold">{{ $organization->legal_name ?: '—' }}</div>
            </div>
            
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