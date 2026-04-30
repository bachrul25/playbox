<div>
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
        <h4 class="pb-section-title mb-0"><i class="bi bi-people"></i> Manajemen User</h4>
        <button class="btn btn-pb-primary" wire:click="openCreate"><i class="bi bi-plus-lg"></i> Tambah User</button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Cari nama / email..." wire:model.live.debounce.400ms="search">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select class="form-select" wire:model.live="roleFilter">
                        <option value="">Semua Role</option>
                        <option value="admin">Admin</option>
                        <option value="owner">Owner</option>
                        <option value="mitra">Mitra</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Mitra</th>
                            <th>Aktif</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr>
                                <td class="fw-semibold">{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td><span class="badge bg-secondary text-uppercase">{{ $u->role }}</span></td>
                                <td>{{ $u->partner?->cafe_name ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $u->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $u->is_active ? 'Ya' : 'Tidak' }}</span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary" wire:click="openEdit({{ $u->id }})"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-outline-danger"
                                        onclick="Swal.fire({title:'Hapus user?',icon:'warning',showCancelButton:true,confirmButtonColor:'#ef476f',confirmButtonText:'Ya, hapus'}).then(r=>{ if(r.isConfirmed) @this.call('delete', {{ $u->id }}); })">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada user.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-2">{{ $users->links() }}</div>
        </div>
    </div>

    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.45);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingId ? 'Edit User' : 'Tambah User' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model.defer="email">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Password {{ $editingId ? '(kosongkan jika tidak diubah)' : '' }}</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" wire:model.defer="password">
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select" wire:model.live="role">
                                        <option value="admin">Admin</option>
                                        <option value="owner">Owner</option>
                                        <option value="mitra">Mitra</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Aktif</label>
                                    <select class="form-select" wire:model.defer="is_active">
                                        <option value="1">Ya</option>
                                        <option value="0">Tidak</option>
                                    </select>
                                </div>
                                @if($role === 'mitra')
                                    <div class="col-12">
                                        <label class="form-label">Mitra/Cafe <span class="text-danger">*</span></label>
                                        <select class="form-select @error('partner_id') is-invalid @enderror" wire:model.defer="partner_id">
                                            <option value="">- Pilih Mitra -</option>
                                            @foreach($partners as $pt)
                                                <option value="{{ $pt->id }}">{{ $pt->cafe_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('partner_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Batal</button>
                            <button type="submit" class="btn btn-pb-primary"><i class="bi bi-save"></i> Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
