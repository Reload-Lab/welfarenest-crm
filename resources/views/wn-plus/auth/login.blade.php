@extends('layouts.wn-plus')

@section('title', 'Login WN+')

@section('content')
<div class="container py-5">
    <div class="card mx-auto border-0 shadow-sm" style="max-width: 460px;">
        <div class="card-body p-4">
            <h1 class="h4 mb-3">Accesso Welfare Nest Plus</h1>

            <form method="POST" action="{{ route('wn-plus.login.post') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        required
                        autofocus
                    >

                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Accedi
                </button>
            </form>
        </div>
    </div>
</div>
@endsection