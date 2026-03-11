<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welfarenest CRM</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">

            <a class="navbar-brand" href="/">Welfarenest CRM</a>

            <div class="collapse navbar-collapse">

                <ul class="navbar-nav me-auto">

                    <li class="nav-item">
                        <a class="nav-link" href="/">Dashboard</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/clients">Clienti</a>
                    </li>

                </ul>

            </div>

        </div>
    </nav>


    <div class="container py-4">
        <header class="mb-4">
            <h1 class="h3">Welfarenest CRM</h1>
        </header>
        <main>
            @yield('content')
        </main>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>