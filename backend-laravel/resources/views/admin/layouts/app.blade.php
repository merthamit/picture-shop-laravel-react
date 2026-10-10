<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
    @yield('styles')
    <title>@yield('title') · Picture Shop Admin</title>
</head>

<body>
    @if (auth()->guard('admin')->check())
        <div class="admin-shell">
            @include('admin.layouts.sidebar')
            <div class="admin-main">
                @include('admin.layouts.header')
                <main class="admin-content">
                    @include('admin.layouts.alerts')
                    @yield('content')
                </main>
            </div>
        </div>
    @else
        @yield('content')
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirm destructive actions (forms with data-confirm)
        document.addEventListener('submit', function(event) {
            const message = event.target.dataset.confirm;
            if (message && !window.confirm(message)) {
                event.preventDefault();
            }
        });
    </script>
    @yield('script')
</body>

</html>
