@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Icone CRM</h1>
            <p class="text-muted mb-0">Catalogo automatico generato da config/icons.php</p>
        </div>
    </div>

    @foreach($icons as $group => $groupIcons)
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">{{ ucfirst($group) }}</h2>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($groupIcons as $name => $component)
                        <div class="col-md-4 col-lg-3">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="fs-3 me-3">
                                        <x-icon :group="$group" :name="$name" />
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $name }}</div>
                                        <div class="text-muted small">{{ $component }}</div>
                                    </div>
                                </div>

                                <label class="form-label small text-muted mb-1">Blade</label>
                                <input
                                    type="text"
                                    class="form-control form-control-sm"
                                    readonly
                                    value='<x-icon group="{{ $group }}" name="{{ $name }}" />'
                                >
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection