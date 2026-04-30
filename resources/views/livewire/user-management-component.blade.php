<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h4 class="fw-bold mb-0">User Management</h4><small class="text-muted">Kelola akun pengguna sistem.</small></div>
        <button class="btn btn-primary" wire:click="openCreate"><i class="bi bi-plus-lg"></i> Tambah User</button>
    </div>

    <div class="card card-summary p-3">
        <div class="row g-2 mb-3">
            <div class="col-md-5"><input type="text" class="form-control" placeholder="Cari nama / email..." wire:model.live.debounce.300ms="search"></div>
            <div class="col-md-3">
                <select class="form-select" wire:model.live="filterRole">
                    <option value="">Semua role</option>
                    <option value="admin">Admin</option>
                    <option value="owner">Owner</option>
                    <option value="kasir">Kasir</option>
                    <option value="operator">Operator</option>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @forelse($users as $u)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar" style="width:32px;height:32px;font-size:.85rem">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                            <strong>{{ $u->name }}</strong>
                        </div>
                    </td>
                    <td class="text-muted">{{ $u->email }}</td>
                    <td>
                        @php $roleColors = ['admin' => 'danger', 'owner' => 'warning text-dark', 'kasir' => 'success', 'operator' => 'primary']; @endphp
                        <span class="badge bg-{{ $roleColors[$u->role] ?? 'secondary' }}">{{ strtoupper($u->role) }}</span>
                    </td>
                    <td><span class="badge bg-{{ $u->status === 'active' ? 'success' : 'secondary' }}">{{ $u->status }}</span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" wire:click="openEdit({{ $u->id }})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" data-livewire-action="deleteUser" data-id="{{ $u->id }}" onclick="confirmDelete(this)"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada user.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $users->links() }}</div>
    </div>

    @if($showModal)
    <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form wire:submit.prevent="save">
                    <div class="modal-header"><h5 class="modal-title">{{ $editingId ? 'Edit' : 'Tambah' }} User</h5><button type="button" class="btn-close" wire:click="closeModal"></button></div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Nama</label><input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control @error('email') is-invalid @enderror" wire:model.defer="email">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="mb-3"><label class="form-label">Password {{ $editingId ? '(kosongkan jika tidak diubah)' : '' }}</label><input type="password" class="form-control @error('password') is-invalid @enderror" wire:model.defer="password">@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Role</label>
                                <select class="form-select" wire:model.defer="role">
                                    <option value="admin">Admin</option>
                                    <option value="owner">Owner</option>
                                    <option value="kasir">Kasir</option>
                                    <option value="operator">Operator</option>
                                </select>
                            </div>
                            <div class="col-md-6"><label class="form-label">Status</label>
                                <select class="form-select" wire:model.defer="status">
                                    <option value="active">Aktif</option><option value="inactive">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-light" wire:click="closeModal">Batal</button><button class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan</button></div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
