@php
    use App\Support\Rupiah;
    $isAdmin = auth()->user()?->role === 'admin';
@endphp
<div>
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
        <h4 class="pb-section-title mb-0"><i class="bi bi-controller"></i> Data PlayBox</h4>
        @if($isAdmin)
            <button class="btn btn-pb-primary" wire:click="openCreate">
                <i class="bi bi-plus-lg"></i> Tambah PlayBox
            </button>
        @endif
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Cari kode/nama/lokasi..." wire:model.live.debounce.400ms="search">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">Semua Status</option>
                        <option value="tersedia">Tersedia</option>
                        <option value="disewa">Disewa</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="tidak_aktif">Tidak Aktif</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select class="form-select" wire:model.live="ownershipFilter">
                        <option value="">Semua Tipe</option>
                        <option value="pribadi">Pribadi</option>
                        <option value="kerjasama">Kerjasama</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Tipe</th>
                            <th>Mitra/Cafe</th>
                            <th>Lokasi</th>
                            <th>Tarif/Jam</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($playboxes as $p)
                            <tr>
                                <td class="fw-semibold">{{ $p->code }}</td>
                                <td>{{ $p->name }}</td>
                                <td>
                                    <span class="badge {{ $p->ownership_type === 'pribadi' ? 'bg-primary' : 'bg-success' }} text-uppercase">{{ $p->ownership_type }}</span>
                                </td>
                                <td>{{ $p->partner?->cafe_name ?? '-' }}</td>
                                <td>{{ $p->location ?: '-' }}</td>
                                <td>{{ Rupiah::format($p->default_price_per_hour) }}</td>
                                <td><span class="badge bg-{{ $p->status }}">{{ str_replace('_',' ', $p->status) }}</span></td>
                                <td class="text-end">
                                    @if($isAdmin)
                                        <button class="btn btn-sm btn-outline-primary" wire:click="openEdit({{ $p->id }})">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"
                                                onclick="Swal.fire({title:'Hapus PlayBox?',icon:'warning',showCancelButton:true,confirmButtonColor:'#ef476f',confirmButtonText:'Ya, hapus'}).then(r=>{ if(r.isConfirmed) @this.call('delete', {{ $p->id }}); })">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-secondary py-4">Belum ada data PlayBox.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-2">{{ $playboxes->links() }}</div>
        </div>
    </div>

    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.45);" wire:keydown.escape="$set('showModal', false)">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingId ? 'Edit PlayBox' : 'Tambah PlayBox' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Kode <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" wire:model.defer="code" placeholder="PBX001">
                                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tipe Kepemilikan</label>
                                    <select class="form-select" wire:model.live="ownership_type">
                                        <option value="pribadi">Pribadi</option>
                                        <option value="kerjasama">Kerjasama</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Mitra/Cafe {{ $ownership_type === 'kerjasama' ? '(wajib)' : '' }}</label>
                                    <select class="form-select @error('partner_id') is-invalid @enderror" wire:model.defer="partner_id" {{ $ownership_type === 'pribadi' ? 'disabled' : '' }}>
                                        <option value="">- Pilih Mitra -</option>
                                        @foreach($partners as $pt)
                                            <option value="{{ $pt->id }}">{{ $pt->cafe_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('partner_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Lokasi</label>
                                    <input type="text" class="form-control" wire:model.defer="location">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" wire:model.defer="status">
                                        <option value="tersedia">Tersedia</option>
                                        <option value="disewa">Disewa</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="tidak_aktif">Tidak Aktif</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tarif/Jam (Rp)</label>
                                    <input type="number" min="0" step="500" class="form-control @error('default_price_per_hour') is-invalid @enderror" wire:model.defer="default_price_per_hour">
                                    @error('default_price_per_hour') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Catatan Kondisi</label>
                                    <textarea class="form-control" rows="2" wire:model.defer="condition_note"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Batal</button>
                            <button type="submit" class="btn btn-pb-primary">
                                <span wire:loading.remove wire:target="save"><i class="bi bi-save"></i> Simpan</span>
                                <span wire:loading wire:target="save"><i class="bi bi-arrow-clockwise"></i> Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
