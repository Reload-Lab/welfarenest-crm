<div class="card border-0 shadow-sm crm-card--header-actions">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <x-crm.avatar
                        :name="$organization->display_name"
                        :image="$organization->avatar_url"
                        type="organization"
                        size="sm"
                    />
                    <h2 class="h4 mb-0">
                        {{ $organization->name ?: '—' }}
                    </h2>

                </div>

            </div>

            <div class="d-flex flex-wrap align-items-start gap-2">

                @include('components.crm.row-actions', [
                    'edit' => route('organizations.edit', $organization),
                    'delete' => route('organizations.destroy', $organization),
                    'deleteConfirm' => 'Confermi l\'eliminazione di questa organizzazione?',
                ])

            </div>
        </div>
    </div>
</div>