@php($user = auth()->user())
<aside class="pb-sidebar">
    <div class="brand">
        <i class="bi bi-controller fs-4 text-info"></i>
        <span>PlayBox Rental</span>
    </div>
    <nav class="nav flex-column mt-2">
        <a href="{{ route('dashboard') }}" wire:navigate class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        @if($user && in_array($user->role, ['admin','owner']))
            <a href="{{ route('playboxes') }}" wire:navigate class="nav-link {{ request()->routeIs('playboxes') ? 'active' : '' }}">
                <i class="bi bi-controller"></i> Data PlayBox
            </a>
            <a href="{{ route('partners') }}" wire:navigate class="nav-link {{ request()->routeIs('partners') ? 'active' : '' }}">
                <i class="bi bi-shop"></i> Data Mitra/Cafe
            </a>
        @endif
        @if($user && $user->role === 'admin')
            <a href="{{ route('rentals') }}" wire:navigate class="nav-link {{ request()->routeIs('rentals') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Transaksi Rental
            </a>
        @endif
        @if($user && in_array($user->role, ['admin','owner']))
            <a href="{{ route('expenses') }}" wire:navigate class="nav-link {{ request()->routeIs('expenses') ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i> Biaya
            </a>
            <a href="{{ route('reports.private') }}" wire:navigate class="nav-link {{ request()->routeIs('reports.private') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph"></i> Laporan Pribadi
            </a>
        @endif
        <a href="{{ route('reports.partnership') }}" wire:navigate class="nav-link {{ request()->routeIs('reports.partnership') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-spreadsheet"></i> Laporan Kerjasama
        </a>
        @if($user && $user->role === 'admin')
            <a href="{{ route('users') }}" wire:navigate class="nav-link {{ request()->routeIs('users') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Manajemen User
            </a>
        @endif
        <form method="POST" action="{{ route('logout') }}" class="px-3 mt-3">
            @csrf
            <button type="submit" class="btn btn-outline-light w-100">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </nav>
</aside>
