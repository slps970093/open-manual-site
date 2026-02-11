<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('description', 'Technical documentation manuals')">
    <meta name="keywords" content="documentation, manuals, technical guides">
    <meta property="og:title" content="@yield('title', 'Documentation')">
    <meta property="og:description" content="@yield('description', 'Technical documentation manuals')">

    <title>@yield('title', 'Documentation') - Manual System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- App CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="{{ route('frontend.manuals') }}">
                <i class="fas fa-book me-2"></i>Documentation
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('frontend.manuals') }}">Manuals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('frontend.search') }}">Search</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main role="main">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-light border-top mt-5 py-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 text-center text-muted">
                    <p class="mb-0">&copy; {{ date('Y') }} Documentation System. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- App JS -->
    <script src="{{ asset('js/app.js') }}"></script>

    @stack('scripts')
</body>
</html>
