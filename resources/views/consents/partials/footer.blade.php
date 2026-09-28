{{--
    Piede della tabella del registro: righe per pagina e paginazione.
    Attesa: $rows (paginator), $indexRoute, $filters.
--}}
<div class="card-footer crm-table-footer">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

        <form method="GET" action="{{ route($indexRoute) }}" class="d-flex align-items-center gap-2">
            @foreach($filters as $key => $value)
                @if($key !== 'per_page')
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach

            <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach([10, 20, 50, 100] as $option)
                    <option value="{{ $option }}" {{ (int) ($filters['per_page'] ?: 20) === $option ? 'selected' : '' }}>
                        {{ $option }} righe
                    </option>
                @endforeach
            </select>
        </form>

        <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
            @if($rows->hasPages())
                <div class="crm-pagination">{{ $rows->links() }}</div>
            @else
                <span class="crm-text-muted small">{{ $rows->total() }} risultati trovati</span>
            @endif
        </div>

    </div>
</div>
