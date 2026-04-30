@php use App\Helpers\FormatHelper as F; @endphp
<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="module-tag module-finance">Keuangan</span>
            <h4 class="fw-bold mb-0 mt-2">Pengeluaran</h4>
            <small class="text-muted">Catat semua pengeluaran usaha.</small>
        </div>
        <button class="btn btn-danger" wire:click="openCreate"><i class="bi bi-plus-lg me-1"></i> Tambah Pengeluaran</button>
    </div>

    <div class="card card-summary p-3">
        <div class="row g-2 mb-3">
            <div class="col-md-3"><input type="text" class="form-control" placeholder="Cari deskripsi..." wire:model.live.debounce.300ms="search"></div>
            <div class="col-md-3">
                <select class="form-select" wire:model.live="filterCategory">
                    <option value="">Semua kategori</option>
                    @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3"><input type="date" class="form-control" wire:model.live="startDate"></div>
            <div class="col-md-3"><input type="date" class="form-control" wire:model.live="endDate"></div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Tanggal</th><th>Kategori</th><th>Deskripsi</th><th>Jumlah</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @forelse($expenses as $e)
                <tr>
                    <td>{{ $e->date->format('d/m/Y') }}</td>
                    <td><span class="badge bg-warning text-dark">{{ $e->category->name ?? '-' }}</span></td>
                    <td>{{ $e->description ?? '-' }}</td>
                    <td class="fw-bold text-danger">{{ F::rupiah($e->amount) }}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" wire:click="openEdit({{ $e->id }})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" data-livewire-action="deleteExpense" data-id="{{ $e->id }}" onclick="confirmDelete(this)"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada pengeluaran.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $expenses->links() }}</div>
    </div>

    @if($showModal)
    <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form wire:submit.prevent="save">
                    <div class="modal-header"><h5 class="modal-title">{{ $editingId ? 'Edit' : 'Tambah' }} Pengeluaran</h5><button type="button" class="btn-close" wire:click="closeModal"></button></div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Kategori</label>
                            <select class="form-select" wire:model.defer="category_id">
                                <option value="">- pilih -</option>
                                @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">Jumlah</label>
                            <input type="number" step="any" min="0" class="form-control @error('amount') is-invalid @enderror" wire:model.defer="amount">
                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3"><label class="form-label">Tanggal</label><input type="date" class="form-control" wire:model.defer="date"></div>
                        <div class="mb-1"><label class="form-label">Deskripsi</label><textarea class="form-control" rows="2" wire:model.defer="description"></textarea></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-light" wire:click="closeModal">Batal</button><button class="btn btn-danger"><i class="bi bi-check-lg"></i> Simpan</button></div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
