@php
    $isAdmin = auth()->user()?->role === 'admin';
@endphp
<div>
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
        <h4 class="pb-section-title mb-0"><i class="bi bi-shop"></i> Data Mitra/Cafe</h4>
        @if($isAdmin)
            <button class="btn btn-pb-primary" wire:click="openCreate">
                <i class="bi bi-plus-lg"></i> Tambah Mitra
            </button>
        @endif
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Cari nama cafe / penanggung jawab..." wire:model.live.debounce.400ms="search">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="tidak_aktif">Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Nama Cafe</th>
                            <th>Penanggung Jawab</th>
                            <th>Telepon</th>
                            <th>Alamat</th>
                            <th>Mulai Kerjasama</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partners as $p)
                            <tr>
                                <td class="fw-semibold">{{ $p->cafe_name }}</td>
                                <td>{{ $p->person_in_charge }}</td>
                                <td>{{ $p->phone ?: '-' }}</td>
                                <td>{{ $p->address ?: '-' }}</td>
                                <td>{{ optional($p->cooperation_start_date)->format('d-m-Y') ?: '-' }}</td>
                                <td>
                                    <span class="badge {{ $p->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">{{ $p->status === 'aktif' ? 'Aktif' : 'Tidak Aktif' }}</span>
                                </td>
                                <td class="text-end">
                                    @if($isAdmin)
                                        <button class="btn btn-sm btn-outline-primary" wire:click="openEdit({{ $p->id }})"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-outline-danger"
                                                onclick="Swal.fire({title:'Hapus mitra?',icon:'warning',showCancelButton:true,confirmButtonColor:'#ef476f',confirmButtonText:'Ya, hapus'}).then(r=>{ if(r.isConfirmed) @this.call('delete', {{ $p->id }}); })">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-secondary py-4">Belum ada mitra.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-2">{{ $partners->links() }}</div>
        </div>
    </div>

    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.45);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingId ? 'Edit Mitra' : 'Tambah Mitra' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Cafe <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('cafe_name') is-invalid @enderror" wire:model.defer="cafe_name">
                                    @error('cafe_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Penanggung Jawab <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('person_in_charge') is-invalid @enderror" wire:model.defer="person_in_charge">
                                    @error('person_in_charge') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Telepon</label>
                                    <input type="text" class="form-control" wire:model.defer="phone">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Alamat</label>
                                    <input type="text" class="form-control" wire:model.defer="address">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tgl Mulai Kerjasama</label>
                                    <input type="date" class="form-control" wire:model.defer="cooperation_start_date">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" wire:model.defer="status">
                                        <option value="aktif">Aktif</option>
                                        <option value="tidak_aktif">Tidak Aktif</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Catatan</label>
                                    <textarea class="form-control" rows="2" wire:model.defer="note"></textarea>
                                </div>
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
