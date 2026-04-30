<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h4 class="fw-bold mb-0">Metode Pembayaran</h4><small class="text-muted">Cash, transfer, QRIS, dll.</small></div>
        <button class="btn btn-primary" wire:click="openCreate"><i class="bi bi-plus-lg"></i> Tambah</button>
    </div>

    <div class="card card-summary p-3">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Nama</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @forelse($methods as $m)
                <tr>
                    <td class="fw-semibold"><i class="bi bi-credit-card-2-front me-1 text-primary"></i> {{ $m->name }}</td>
                    <td><span class="badge bg-{{ $m->status === 'active' ? 'success' : 'secondary' }}">{{ $m->status }}</span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" wire:click="openEdit({{ $m->id }})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" data-livewire-action="deleteMethod" data-id="{{ $m->id }}" onclick="confirmDelete(this)"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-muted text-center py-4">Belum ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $methods->links() }}</div>
    </div>

    @if($showModal)
    <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form wire:submit.prevent="save">
                    <div class="modal-header"><h5 class="modal-title">{{ $editingId ? 'Edit' : 'Tambah' }} Metode Pembayaran</h5><button type="button" class="btn-close" wire:click="closeModal"></button></div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Nama</label><input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
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
