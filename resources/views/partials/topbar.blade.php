@php($user = auth()->user())
<div class="pb-topbar">
    <button id="pbSidebarToggle" class="btn btn-outline-secondary d-lg-none" type="button">
        <i class="bi bi-list"></i>
    </button>
    <div class="fw-semibold text-secondary small text-uppercase">
        {{ $title ?? 'PlayBox Rental Management System' }}
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="text-end small d-none d-md-block">
            <div class="fw-semibold">{{ $user->name ?? 'Guest' }}</div>
            <div class="text-secondary">
                <span class="badge bg-secondary text-uppercase">{{ $user->role ?? '-' }}</span>
            </div>
        </div>
        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
            <i class="bi bi-person"></i>
        </div>
    </div>
</div>
