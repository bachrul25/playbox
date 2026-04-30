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
        :root {
            --pb-primary: #0d3b66;
            --pb-primary-dark: #082849;
            --pb-accent: #06a77d;
            --pb-bg: #f4f6fa;
        }
        body { background: var(--pb-bg); font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
        .pb-sidebar {
            background: linear-gradient(180deg, var(--pb-primary), var(--pb-primary-dark));
            min-height: 100vh; color: #fff; width: 250px;
            position: fixed; top: 0; left: 0; bottom: 0; padding: 1rem 0; z-index: 1030;
            transition: transform .25s ease;
        }
        .pb-sidebar .brand { font-weight: 700; font-size: 1.15rem; padding: .5rem 1.25rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,.1); display:flex; align-items:center; gap:.5rem; }
        .pb-sidebar a.nav-link {
            color: rgba(255,255,255,.85); padding: .65rem 1.25rem; display: flex; align-items: center; gap: .65rem; border-radius: 0;
        }
        .pb-sidebar a.nav-link:hover { background: rgba(255,255,255,.08); color: #fff; }
        .pb-sidebar a.nav-link.active { background: rgba(6,167,125,.18); border-left: 3px solid var(--pb-accent); color: #fff; font-weight: 600; }
        .pb-content { margin-left: 250px; min-height: 100vh; }
        .pb-topbar {
            background: #fff; border-bottom: 1px solid #e9ecef; padding: .75rem 1.5rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 1020;
        }
        .pb-card-stat { border: 0; border-radius: .75rem; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
        .pb-card-stat .icon-wrap { width: 48px; height: 48px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.4rem; }
        .pb-section-title { color: var(--pb-primary); font-weight: 700; }
        .badge.bg-tersedia { background: #06a77d !important; }
        .badge.bg-disewa { background: #f0a500 !important; }
        .badge.bg-maintenance { background: #ef476f !important; }
        .badge.bg-tidak_aktif { background: #6c757d !important; }
        .table thead th { background: #f8fafc; color: var(--pb-primary); font-weight: 600; }
        .btn-pb-primary { background: var(--pb-primary); color: #fff; border-color: var(--pb-primary); }
        .btn-pb-primary:hover { background: var(--pb-primary-dark); color: #fff; border-color: var(--pb-primary-dark); }
        .btn-pb-accent { background: var(--pb-accent); color: #fff; border-color: var(--pb-accent); }
        .btn-pb-accent:hover { background: #05876a; color: #fff; border-color: #05876a; }
        @media (max-width: 991.98px) {
            .pb-sidebar { transform: translateX(-100%); }
            .pb-sidebar.show { transform: translateX(0); }
            .pb-content { margin-left: 0; }
        }
    </style>

    @livewireStyles
</head>
<body>
    @auth
        @include('partials.sidebar')
        <div class="pb-content">
            @include('partials.topbar')
            <main class="p-3 p-md-4">
                {{ $slot ?? '' }}
            </main>
        </div>
    @else
        {{ $slot ?? '' }}
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    @livewireScripts

    <script>
        // Toast helper for Livewire dispatch('toast')
        const PbToast = (type, message) => {
            const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false,
                timer: 3000, timerProgressBar: true,
            });
            Toast.fire({ icon: type || 'info', title: message });
        };

        document.addEventListener('livewire:init', () => {
            Livewire.on('toast', ({ type, message }) => PbToast(type, message));
            Livewire.on('confirm-delete', (params) => {
                const { id, message, method } = params;
                Swal.fire({
                    title: 'Yakin ingin menghapus?',
                    text: message || 'Tindakan ini tidak dapat dibatalkan.',
                    icon: 'warning', showCancelButton: true,
                    confirmButtonColor: '#ef476f', cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal',
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch(method || 'do-delete', { id });
                    }
                });
            });
        });

        // Sidebar toggle (mobile)
        document.addEventListener('click', (e) => {
            if (e.target.closest('#pbSidebarToggle')) {
                document.querySelector('.pb-sidebar')?.classList.toggle('show');
            }
        });
    </script>
</body>
</html>
