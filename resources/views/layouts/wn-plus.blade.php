<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'CRM') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="w-100" style="max-width: 420px;">
            <div class="text-center mb-4">
                <div class="fw-bold fs-4"><img src="/images/logo-wn-plus.svg" alt="Logo" class="img-fluid"></div>
            </div>

            @yield('content')
        </div>
    </div>
</body>
</html>