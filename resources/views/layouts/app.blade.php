<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'CRM') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="crmApp" class="crm-app">
        @include('layouts.partials.sidebar')

        <div class="crm-sidebar-backdrop" id="crmSidebarBackdrop"></div>

        <div class="crm-main">
            @include('layouts.partials.topbar')

            <main class="crm-content">

                @hasSection('pageHeader')
                    <div class="crm-page-header">
                        @yield('pageHeader')
                    </div>
                @endif

                {{-- SUCCESS MESSAGE --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mx-4 mt-3" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- ERROR MESSAGE --}}
                @if ($errors->any())
                    <div class="alert alert-danger mx-4 mt-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const crmApp = document.getElementById('crmApp');
            const sidebarToggleDesktop = document.getElementById('sidebarToggleDesktop');
            const sidebarToggleMobile = document.getElementById('sidebarToggleMobile');
            const sidebarBackdrop = document.getElementById('crmSidebarBackdrop');

            if (sidebarToggleDesktop) {
                sidebarToggleDesktop.addEventListener('click', function () {
                    if (window.innerWidth >= 992) {
                        crmApp.classList.toggle('sidebar-collapsed');
                    } else {
                        crmApp.classList.toggle('sidebar-mobile-open');
                    }
                });
            }

            if (sidebarToggleMobile) {
                sidebarToggleMobile.addEventListener('click', function () {
                    crmApp.classList.toggle('sidebar-mobile-open');
                });
            }

            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', function () {
                    crmApp.classList.remove('sidebar-mobile-open');
                });
            }
        });
    </script>
</body>
</html>