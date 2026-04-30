@php use App\Helpers\FormatHelper as F; @endphp
<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="module-tag module-finance">Keuangan</span>
            <h4 class="fw-bold mb-0 mt-2">Pemasukan</h4>
            <small class="text-muted">Pemasukan dari POS, rental, dan input manual.</small>
        </div>
        <button class="btn btn-success" wire:click="openCreate"><i class="bi bi-plus-lg me-1"></i> Tambah Pemasukan Manual</button>
    </div>

    <div class="card card-summary p-3">
        <div class="row g-2 mb-3">
            <div class="col-md-3"><input type="text" class="form-control" placeholder="Cari deskripsi..." wire:model.live.debounce.300ms="search"></div>
            <div class="col-md-3">
                <select class="form-select" wire:model.live="filterSource">
                    <option value="">Semua sumber</option>
                    <option value="pos">POS</option>
                    <option value="rental">Rental</option>
                    <option value="manual">Manual</option>
                </select>
            </div>
            <div class="col-md-3"><input type="date" class="form-control" wire:model.live="startDate"></div>
            <div class="col-md-3"><input type="date" class="form-control" wire:model.live="endDate"></div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Tanggal</th><th>Sumber</th><th>Kategori</th><th>Deskripsi</th><th>Jumlah</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @forelse($incomes as $i)
                <tr>
                    <td>{{ $i->date->format('d/m/Y') }}</td>
                    <td>
                        @php $colors = ['pos' => 'success', 'rental' => 'primary', 'manual' => 'info text-dark']; @endphp
                        <span class="badge bg-{{ $colors[$i->source] ?? 'secondary' }}">{{ ucfirst($i->source) }}</span>
                    </td>
                    <td>{{ $i->category->name ?? '-' }}</td>
                    <td>{{ $i->description ?? '-' }}</td>
                    <td class="fw-bold text-success">{{ F::rupiah($i->amount) }}</td>
                    <td class="text-end">
                        @if($i->source === 'manual')
                        <button class="btn btn-sm btn-outline-primary" wire:click="openEdit({{ $i->id }})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" data-livewire-action="deleteIncome" data-id="{{ $i->id }}" onclick="confirmDelete(this)"><i class="bi bi-trash"></i></button>
                        @else
                        <span class="badge bg-light text-muted border">otomatis</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada pemasukan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $incomes->links() }}</div>
    </div>

    @if($showModal)
    <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form wire:submit.prevent="save">
                    <div class="modal-header"><h5 class="modal-title">{{ $editingId ? 'Edit' : 'Tambah' }} Pemasukan Manual</h5><button type="button" class="btn-close" wire:click="closeModal"></button></div>
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
                        <div class="mb-3"><label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" wire:model.defer="date">
                        </div>
                        <div class="mb-1"><label class="form-label">Deskripsi</label>
                            <textarea class="form-control" rows="2" wire:model.defer="description"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-light" wire:click="closeModal">Batal</button><button class="btn btn-success"><i class="bi bi-check-lg"></i> Simpan</button></div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
