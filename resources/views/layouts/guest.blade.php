<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'PlayBox Rental Management System' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0d3b66 0%, #06a77d 100%);
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
        }
        .login-card { background: #fff; border-radius: 1rem; box-shadow: 0 20px 60px rgba(0,0,0,.25); width: 100%; max-width: 420px; padding: 2.5rem 2rem; }
        .login-brand { display:flex; align-items:center; justify-content:center; gap:.5rem; color:#0d3b66; font-weight: 700; font-size: 1.4rem; margin-bottom: .25rem; }
    </style>
    @livewireStyles
</head>
<body>
    {{ $slot ?? '' }}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>
</html>
