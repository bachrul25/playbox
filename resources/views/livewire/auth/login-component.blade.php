<div class="login-card">
    <div class="login-brand">
        <i class="bi bi-controller fs-2 text-success"></i>
        PlayBox Rental
    </div>
    <p class="text-center text-secondary mb-4 small">Login untuk masuk sistem manajemen rental.</p>

    @if(session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
    @endif

    <form wire:submit="login">
        <div class="mb-3">
            <label class="form-label small fw-semibold">Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model.defer="email" placeholder="email@playbox.com" autocomplete="username" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-semibold">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control @error('password') is-invalid @enderror" wire:model.defer="password" placeholder="••••••••" autocomplete="current-password" required>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="remember" wire:model.defer="remember">
            <label class="form-check-label small" for="remember">Ingat saya</label>
        </div>

        <button type="submit" class="btn btn-pb-primary w-100 fw-semibold" wire:loading.attr="disabled" style="background:#0d3b66;border-color:#0d3b66;color:#fff;">
            <span wire:loading.remove><i class="bi bi-box-arrow-in-right me-1"></i> Login</span>
            <span wire:loading><i class="bi bi-arrow-clockwise"></i> Memproses...</span>
        </button>
    </form>

    <div class="mt-4 small text-secondary">
        <div class="fw-semibold mb-1">Akun demo:</div>
        <ul class="ps-3 mb-0">
            <li>admin@playbox.com / password</li>
            <li>owner@playbox.com / password</li>
            <li>mitra@playbox.com / password</li>
        </ul>
    </div>
</div>
