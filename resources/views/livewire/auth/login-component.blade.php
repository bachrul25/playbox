<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5 col-xl-4">
        <div class="login-card p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="brand-icon"><i class="bi bi-controller"></i></div>
                <h4 class="fw-bold mb-1">Sistem Manajemen Usaha Terpadu</h4>
                <p class="text-muted mb-0 small">POS · Rental Playbox · Keuangan</p>
            </div>

            <form wire:submit.prevent="login" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">EMAIL</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                        <input type="email" wire:model.defer="email" class="form-control @error('email') is-invalid @enderror" placeholder="email@gmail.com" required autofocus>
                    </div>
                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">PASSWORD</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                        <input type="password" wire:model.defer="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required>
                    </div>
                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" wire:model="remember" id="remember" class="form-check-input">
                    <label for="remember" class="form-check-label small">Ingat saya</label>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                    <span wire:loading.remove wire:target="login"><i class="bi bi-box-arrow-in-right me-1"></i> Masuk</span>
                    <span wire:loading wire:target="login"><span class="spinner-border spinner-border-sm"></span> Memproses...</span>
                </button>
            </form>

            <hr class="my-4">
            <div class="small text-muted mb-2">Akun demo (password: <code>password</code>):</div>
            <div class="d-flex flex-wrap gap-1">
                <span class="badge text-bg-danger demo-chip">admin@gmail.com</span>
                <span class="badge text-bg-warning demo-chip">owner@gmail.com</span>
                <span class="badge text-bg-success demo-chip">kasir@gmail.com</span>
                <span class="badge text-bg-primary demo-chip">operator@gmail.com</span>
            </div>
        </div>
        <p class="text-center text-white-50 mt-3 small">&copy; {{ date('Y') }} Sistem Manajemen Usaha Terpadu</p>
    </div>
</div>
