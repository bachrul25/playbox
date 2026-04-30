@php use App\Helpers\FormatHelper as F; @endphp
<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="module-tag module-rental">Rental</span>
            <h4 class="fw-bold mb-0 mt-2">Unit Rental Playbox</h4>
            <small class="text-muted">Master data unit PS / Playbox.</small>
        </div>
        <button class="btn btn-primary" wire:click="openCreate"><i class="bi bi-plus-lg me-1"></i> Tambah Unit</button>
    </div>

    <div class="card card-summary p-3">
        <div class="row g-2 mb-3">
            <div class="col-md-5"><input type="text" class="form-control" placeholder="Cari kode/nama..." wire:model.live.debounce.300ms="search"></div>
            <div class="col-md-3">
                <select class="form-select" wire:model.live="filterStatus">
                    <option value="">Semua status</option>
                    <option value="available">Tersedia</option>
                    <option value="in_use">Disewa</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>Kode</th><th>Nama</th><th>Jenis</th><th>Tarif/Jam</th><th>Lokasi</th><th>Status</th><th class="text-end">Aksi</th></tr>
                </thead>
                <tbody>
                @forelse($units as $u)
                <tr>
                    <td class="fw-bold">{{ $u->code }}</td>
                    <td>{{ $u->name }}</td>
                    <td><span class="badge bg-info text-dark">{{ $u->type }}</span></td>
                    <td>{{ F::rupiah($u->hourly_price) }}</td>
                    <td>{{ $u->location ?? '-' }}</td>
                    <td>
                        @php $colorMap = ['available' => 'success', 'in_use' => 'primary', 'maintenance' => 'warning text-dark', 'inactive' => 'secondary']; @endphp
                        <span class="badge bg-{{ $colorMap[$u->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$u->status)) }}</span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" wire:click="openEdit({{ $u->id }})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" data-livewire-action="deleteUnit" data-id="{{ $u->id }}" onclick="confirmDelete(this)"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada unit.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $units->links() }}</div>
    </div>

    @if($showModal)
    <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form wire:submit.prevent="save">
                    <div class="modal-header"><h5 class="modal-title">{{ $editingId ? 'Edit Unit' : 'Tambah Unit' }}</h5><button type="button" class="btn-close" wire:click="closeModal"></button></div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label">Kode</label><input type="text" class="form-control @error('code') is-invalid @enderror" wire:model.defer="code">@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-md-8"><label class="form-label">Nama Unit</label><input type="text" class="form-control" wire:model.defer="name"></div>
                            <div class="col-md-4"><label class="form-label">Jenis</label>
                                <select class="form-select" wire:model.defer="type">
                                    <option>PS3</option><option>PS4</option><option>PS5</option><option>Playbox</option><option>Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-4"><label class="form-label">Tarif/Jam</label><input type="number" step="any" min="0" class="form-control" wire:model.defer="hourly_price"></div>
                            <div class="col-md-4"><label class="form-label">Status</label>
                                <select class="form-select" wire:model.defer="status">
                                    <option value="available">Tersedia</option>
                                    <option value="in_use">Disewa</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                            </div>
                            <div class="col-md-6"><label class="form-label">Lokasi/Meja</label><input type="text" class="form-control" wire:model.defer="location"></div>
                            <div class="col-md-6"><label class="form-label">Keterangan</label><input type="text" class="form-control" wire:model.defer="description"></div>
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
