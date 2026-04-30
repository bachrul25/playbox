<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Kategori Keuangan</h4>
            <small class="text-muted">Kategori untuk pemasukan dan pengeluaran.</small>
        </div>
        <button class="btn btn-primary" wire:click="openCreate"><i class="bi bi-plus-lg"></i> Tambah Kategori</button>
    </div>

    <div class="card card-summary p-3">
        <div class="row g-2 mb-3">
            <div class="col-md-5"><input type="text" class="form-control" placeholder="Cari nama..." wire:model.live.debounce.300ms="search"></div>
            <div class="col-md-3">
                <select class="form-select" wire:model.live="filterType">
                    <option value="">Semua tipe</option>
                    <option value="income">Pemasukan</option>
                    <option value="expense">Pengeluaran</option>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Nama</th><th>Tipe</th><th>Deskripsi</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @forelse($categories as $c)
                <tr>
                    <td class="fw-semibold">{{ $c->name }}</td>
                    <td><span class="badge bg-{{ $c->type === 'income' ? 'success' : 'danger' }}">{{ ucfirst($c->type) }}</span></td>
                    <td class="text-muted small">{{ $c->description ?? '-' }}</td>
                    <td><span class="badge bg-{{ $c->status === 'active' ? 'primary' : 'secondary' }}">{{ $c->status }}</span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" wire:click="openEdit({{ $c->id }})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" data-livewire-action="deleteCategory" data-id="{{ $c->id }}" onclick="confirmDelete(this)"><i class="bi bi-trash"></i></button>
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
                    <div class="modal-header"><h5 class="modal-title">{{ $editingId ? 'Edit' : 'Tambah' }} Kategori Keuangan</h5><button type="button" class="btn-close" wire:click="closeModal"></button></div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Nama</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3"><label class="form-label">Tipe</label>
                            <select class="form-select" wire:model.defer="type">
                                <option value="income">Pemasukan</option>
                                <option value="expense">Pengeluaran</option>
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">Deskripsi</label><textarea class="form-control" wire:model.defer="description"></textarea></div>
                        <div class="mb-1"><label class="form-label">Status</label>
                            <select class="form-select" wire:model.defer="status">
                                <option value="active">Aktif</option><option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-light" wire:click="closeModal">Batal</button><button class="btn btn-primary"><i class="bi bi-check-lg"></i> Simpan</button></div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
