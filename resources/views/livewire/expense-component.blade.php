@php
    use App\Support\Rupiah;
    $isAdmin = auth()->user()?->role === 'admin';
@endphp
<div>
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
        <h4 class="pb-section-title mb-0"><i class="bi bi-wallet2"></i> Manajemen Biaya</h4>
        @if($isAdmin)
            <button class="btn btn-pb-primary" wire:click="openCreate"><i class="bi bi-plus-lg"></i> Tambah Biaya</button>
        @endif
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Cari deskripsi..." wire:model.live.debounce.400ms="search">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select class="form-select" wire:model.live="typeFilter">
                        <option value="">Semua Jenis</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="perawatan">Perawatan</option>
                        <option value="kerusakan">Kerusakan</option>
                        <option value="staff">Staff</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" class="form-control" wire:model.live="from" placeholder="Dari">
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" class="form-control" wire:model.live="to" placeholder="Sampai">
                </div>
                <div class="col-6 col-md-2 text-end">
                    <div class="bg-light rounded p-2 small">
                        <div class="text-secondary">Total Biaya</div>
                        <div class="fw-bold text-danger">{{ Rupiah::format($totalAmount) }}</div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Nominal</th>
                            <th>PlayBox</th>
                            <th>Mitra</th>
                            <th>Deskripsi</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $e)
                            <tr>
                                <td>{{ $e->expense_date?->format('d-m-Y') }}</td>
                                <td><span class="badge bg-secondary text-uppercase">{{ $e->type }}</span></td>
                                <td class="fw-semibold">{{ Rupiah::format($e->amount) }}</td>
                                <td>{{ $e->playbox?->name ?? '-' }}</td>
                                <td>{{ $e->partner?->cafe_name ?? '-' }}</td>
                                <td>{{ $e->description ?: '-' }}</td>
                                <td class="text-end">
                                    @if($isAdmin)
                                        <button class="btn btn-sm btn-outline-primary" wire:click="openEdit({{ $e->id }})"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-outline-danger"
                                            onclick="Swal.fire({title:'Hapus biaya?',icon:'warning',showCancelButton:true,confirmButtonColor:'#ef476f',confirmButtonText:'Ya, hapus'}).then(r=>{ if(r.isConfirmed) @this.call('delete', {{ $e->id }}); })">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-secondary py-4">Belum ada biaya.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-2">{{ $expenses->links() }}</div>
        </div>
    </div>

    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.45);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingId ? 'Edit Biaya' : 'Tambah Biaya' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('expense_date') is-invalid @enderror" wire:model.defer="expense_date">
                                    @error('expense_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Jenis</label>
                                    <select class="form-select" wire:model.defer="type">
                                        <option value="maintenance">Maintenance</option>
                                        <option value="perawatan">Perawatan</option>
                                        <option value="kerusakan">Kerusakan</option>
                                        <option value="staff">Staff</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nominal (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" min="0" step="500" class="form-control @error('amount') is-invalid @enderror" wire:model.defer="amount">
                                    @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">PlayBox (opsional)</label>
                                    <select class="form-select" wire:model.defer="playbox_id">
                                        <option value="">-</option>
                                        @foreach($playboxList as $pb)
                                            <option value="{{ $pb->id }}">[{{ $pb->code }}] {{ $pb->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mitra (opsional)</label>
                                    <select class="form-select" wire:model.defer="partner_id">
                                        <option value="">-</option>
                                        @foreach($partnerList as $pt)
                                            <option value="{{ $pt->id }}">{{ $pt->cafe_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea class="form-control" rows="2" wire:model.defer="description"></textarea>
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
