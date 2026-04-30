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
        :root {
            --sidebar-width: 250px;
            --color-pos: #16a34a;
            --color-rental: #2563eb;
            --color-finance: #f97316;
            --color-report: #7c3aed;
        }
        body { background: #f3f4f6; font-family: 'Segoe UI', Tahoma, sans-serif; min-height: 100vh; }
        .layout { display: flex; min-height: 100vh; }
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #0f172a, #1e293b);
            color: #cbd5e1;
            position: fixed; top: 0; left: 0; bottom: 0;
            display: flex; flex-direction: column;
            z-index: 1030;
            transition: transform .25s ease;
        }
        .sidebar .brand {
            padding: 18px 20px; border-bottom: 1px solid rgba(255,255,255,.08);
            display: flex; align-items: center; gap: 10px; color: #fff;
        }
        .sidebar .brand .icon {
            width: 38px; height: 38px; border-radius: 10px;
            background: linear-gradient(135deg, #0d6efd, #20c997);
            display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
        }
        .sidebar .nav-section { padding: 14px 18px 4px; font-size: .68rem; letter-spacing: .12em;
            text-transform: uppercase; color: #64748b; }
        .sidebar a.nav-item-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 18px; color: #cbd5e1; text-decoration: none;
            border-left: 3px solid transparent; font-size: .92rem;
            transition: background .15s, border-color .15s;
        }
        .sidebar a.nav-item-link i { width: 20px; text-align: center; }
        .sidebar a.nav-item-link:hover { background: rgba(255,255,255,.05); color: #fff; }
        .sidebar a.nav-item-link.active {
            background: rgba(13,110,253,.18); color: #fff; border-left-color: #0d6efd;
        }
        .sidebar a.nav-item-link.active.pos    { border-left-color: var(--color-pos); background: rgba(22,163,74,.18); }
        .sidebar a.nav-item-link.active.rental { border-left-color: var(--color-rental); background: rgba(37,99,235,.18); }
        .sidebar a.nav-item-link.active.finance{ border-left-color: var(--color-finance); background: rgba(249,115,22,.18); }
        .sidebar a.nav-item-link.active.report { border-left-color: var(--color-report); background: rgba(124,58,237,.18); }
        .sidebar .footer-user {
            margin-top: auto; padding: 14px 18px; border-top: 1px solid rgba(255,255,255,.08);
            display: flex; align-items: center; gap: 10px;
        }
        .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, #0d6efd, #20c997);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 600;
        }
        .topbar {
            background: #ffffff; border-bottom: 1px solid #e5e7eb;
            padding: 12px 24px; display: flex; align-items: center; gap: 12px;
            position: sticky; top: 0; z-index: 1020;
        }
        .main {
            margin-left: var(--sidebar-width); flex: 1;
            display: flex; flex-direction: column; min-width: 0;
        }
        .content { padding: 24px; }
        .badge-role {
            font-size: .7rem; padding: .25rem .55rem;
        }
        .module-tag {
            display: inline-block; padding: 2px 10px; border-radius: 999px;
            font-size: .7rem; font-weight: 600; letter-spacing: .04em;
            color: #fff;
        }
        .module-pos    { background: var(--color-pos); }
        .module-rental { background: var(--color-rental); }
        .module-finance{ background: var(--color-finance); }
        .module-report { background: var(--color-report); }

        .card-summary { border: none; border-radius: 14px; box-shadow: 0 2px 8px rgba(15,23,42,.06); }
        .card-summary .icon-box {
            width: 46px; height: 46px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; font-size: 1.3rem;
        }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main { margin-left: 0; }
        }
        .toggler { display: none; }
        @media (max-width: 992px) { .toggler { display: inline-flex; } }
    </style>
    @livewireStyles
    @stack('styles')
</head>
<body>
    @php $user = auth()->user(); $isAdmin = $user && $user->role === 'admin'; $isOwner = $user && $user->role === 'owner'; $isKasir = $user && $user->role === 'kasir'; $isOperator = $user && $user->role === 'operator'; @endphp

    <div class="layout">
        <aside class="sidebar" id="sidebar">
            <div class="brand">
                <div class="icon"><i class="bi bi-controller"></i></div>
                <div>
                    <div class="fw-bold lh-1">SMU Terpadu</div>
                    <small class="text-muted" style="font-size:.7rem">POS · Rental · Keuangan</small>
                </div>
            </div>

            <div class="nav-section">Utama</div>
            <a class="nav-item-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>

            @if($isAdmin || $isKasir || $isOwner)
            <div class="nav-section">POS / Penjualan</div>
            @if($isAdmin || $isKasir)
            <a class="nav-item-link pos {{ request()->routeIs('pos') ? 'active' : '' }}" href="{{ route('pos') }}"><i class="bi bi-cash-coin"></i> Kasir POS</a>
            @endif
            <a class="nav-item-link pos {{ request()->routeIs('transactions') ? 'active' : '' }}" href="{{ route('transactions') }}"><i class="bi bi-receipt"></i> Riwayat Transaksi</a>
            @endif

            @if($isAdmin || $isOperator || $isOwner)
            <div class="nav-section">Rental Playbox</div>
            @if($isAdmin || $isOperator)
            <a class="nav-item-link rental {{ request()->routeIs('rental') ? 'active' : '' }}" href="{{ route('rental') }}"><i class="bi bi-controller"></i> Rental Aktif</a>
            @endif
            <a class="nav-item-link rental {{ request()->routeIs('rental.history') ? 'active' : '' }}" href="{{ route('rental.history') }}"><i class="bi bi-clock-history"></i> Riwayat Rental</a>
            @endif

            @if($isAdmin || $isOwner)
            <div class="nav-section">Keuangan</div>
            <a class="nav-item-link finance {{ request()->routeIs('finance') ? 'active' : '' }}" href="{{ route('finance') }}"><i class="bi bi-wallet2"></i> Ringkasan Keuangan</a>
            <a class="nav-item-link finance {{ request()->routeIs('incomes') ? 'active' : '' }}" href="{{ route('incomes') }}"><i class="bi bi-arrow-down-circle"></i> Pemasukan</a>
            <a class="nav-item-link finance {{ request()->routeIs('expenses') ? 'active' : '' }}" href="{{ route('expenses') }}"><i class="bi bi-arrow-up-circle"></i> Pengeluaran</a>
            <a class="nav-item-link finance {{ request()->routeIs('cashflows') ? 'active' : '' }}" href="{{ route('cashflows') }}"><i class="bi bi-graph-up-arrow"></i> Arus Kas</a>
            @endif

            @if($isAdmin || $isOwner)
            <div class="nav-section">Master Data</div>
            <a class="nav-item-link {{ request()->routeIs('categories') ? 'active' : '' }}" href="{{ route('categories') }}"><i class="bi bi-tags"></i> Kategori Produk</a>
            <a class="nav-item-link {{ request()->routeIs('products') ? 'active' : '' }}" href="{{ route('products') }}"><i class="bi bi-box-seam"></i> Produk</a>
            <a class="nav-item-link {{ request()->routeIs('rental.units') ? 'active' : '' }}" href="{{ route('rental.units') }}"><i class="bi bi-tv"></i> Unit Rental</a>
            <a class="nav-item-link {{ request()->routeIs('finance.categories') ? 'active' : '' }}" href="{{ route('finance.categories') }}"><i class="bi bi-bookmark"></i> Kategori Keuangan</a>
            <a class="nav-item-link {{ request()->routeIs('payment.methods') ? 'active' : '' }}" href="{{ route('payment.methods') }}"><i class="bi bi-credit-card"></i> Metode Pembayaran</a>
            @endif

            @if($isAdmin || $isOwner)
            <div class="nav-section">Laporan</div>
            <a class="nav-item-link report {{ request()->routeIs('reports') ? 'active' : '' }}" href="{{ route('reports') }}"><i class="bi bi-bar-chart-line"></i> Laporan & Analytics</a>
            @endif

            @if($isAdmin)
            <div class="nav-section">Manajemen</div>
            <a class="nav-item-link {{ request()->routeIs('users') ? 'active' : '' }}" href="{{ route('users') }}"><i class="bi bi-people"></i> User Management</a>
            @endif

            <div class="footer-user">
                <div class="avatar">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</div>
                <div class="flex-grow-1 small">
                    <div class="fw-semibold text-light lh-1">{{ $user->name ?? '-' }}</div>
                    <span class="badge bg-primary badge-role text-uppercase">{{ $user->role ?? '-' }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button class="btn btn-sm btn-outline-light" title="Logout"><i class="bi bi-box-arrow-right"></i></button>
                </form>
            </div>
        </aside>

        <div class="main">
            <header class="topbar">
                <button class="btn btn-light toggler" type="button" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="bi bi-list"></i></button>
                <h6 class="mb-0 fw-semibold flex-grow-1">@yield('page-title', $__livewire_title ?? 'Dashboard')</h6>
                <span class="text-muted small d-none d-md-inline">{{ now()->format('d M Y · H:i') }}</span>
            </header>

            <main class="content">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Global SweetAlert toast helpers usable from Livewire components.
        const Toast = Swal.mixin({
            toast: true, position: 'top-end',
            showConfirmButton: false, timer: 2200,
            timerProgressBar: true,
        });

        document.addEventListener('livewire:init', () => {
            Livewire.on('toast', (data) => {
                const payload = Array.isArray(data) ? data[0] : data;
                Toast.fire({ icon: payload.type || 'success', title: payload.message || '' });
            });
            Livewire.on('alert', (data) => {
                const payload = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    icon: payload.type || 'info',
                    title: payload.title || '',
                    text: payload.message || '',
                });
            });
        });

        // Generic delete confirm: <button onclick="confirmDelete(this)" data-livewire-action="deleteSomething" data-id="...">
        function confirmDelete(el) {
            const action = el.dataset.livewireAction;
            const id = el.dataset.id;
            Swal.fire({
                icon: 'warning', title: 'Yakin hapus?',
                text: 'Data yang dihapus tidak bisa dikembalikan.',
                showCancelButton: true, confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal', confirmButtonColor: '#dc3545',
            }).then((res) => {
                if (res.isConfirmed) {
                    Livewire.dispatch(action, [id]);
                }
            });
        }
    </script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
