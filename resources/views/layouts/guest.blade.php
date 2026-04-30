<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $__livewire_title ?? config('app.name'))</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #6f42c1 0%, #0d6efd 50%, #20c997 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }
        .login-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.96);
            border-radius: 18px;
            box-shadow: 0 25px 60px rgba(0,0,0,.25);
        }
        .brand-icon {
            width: 64px; height: 64px; border-radius: 16px;
            background: linear-gradient(135deg, #0d6efd, #20c997);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1.8rem; margin: 0 auto 1rem;
        }
        .demo-chip {
            font-size: .75rem;
        }
    </style>
    @livewireStyles
</head>
<body>
    <main class="container py-5">
        {{ $slot ?? '' }}
        @yield('content')
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>
</html>
