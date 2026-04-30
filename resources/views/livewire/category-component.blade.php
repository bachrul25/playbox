<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Kategori Produk</h4>
            <small class="text-muted">Kelompokkan produk untuk memudahkan pencarian.</small>
        </div>
        <button class="btn btn-primary" wire:click="openCreate"><i class="bi bi-plus-lg me-1"></i> Tambah Kategori</button>
    </div>

    <div class="card card-summary p-3">
        <div class="mb-3 col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari kategori...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Jumlah Produk</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td class="fw-semibold"><i class="bi bi-tag text-primary me-1"></i> {{ $cat->name }}</td>
                        <td class="text-muted small">{{ $cat->description ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $cat->status === 'active' ? 'success' : 'secondary' }}">{{ $cat->status }}</span>
                        </td>
                        <td>{{ $cat->products()->count() }}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" wire:click="openEdit({{ $cat->id }})"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger" data-livewire-action="deleteCategory" data-id="{{ $cat->id }}" onclick="confirmDelete(this)"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada kategori.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $categories->links() }}</div>
    </div>

    @if($showModal)
    <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form wire:submit.prevent="save">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingId ? 'Edit Kategori' : 'Tambah Kategori' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori</label>
                            <input type="text" wire:model.defer="name" class="form-control @error('name') is-invalid @enderror">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea wire:model.defer="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Status</label>
                            <select wire:model.defer="status" class="form-select">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="closeModal">Batal</button>
                        <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
