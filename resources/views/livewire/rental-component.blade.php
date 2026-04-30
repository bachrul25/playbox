@php
    use App\Support\Rupiah;
    $preview = $this->calculationPreview;
@endphp
<div>
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
        <h4 class="pb-section-title mb-0"><i class="bi bi-receipt"></i> Transaksi Rental</h4>
        <button class="btn btn-pb-primary" wire:click="openCreate"><i class="bi bi-plus-lg"></i> Transaksi Baru</button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Cari invoice / customer / playbox..." wire:model.live.debounce.400ms="search">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select class="form-select" wire:model.live="typeFilter">
                        <option value="">Semua Tipe</option>
                        <option value="pribadi">Pribadi</option>
                        <option value="kerjasama">Kerjasama</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select class="form-select" wire:model.live="paymentStatusFilter">
                        <option value="">Semua Pembayaran</option>
                        <option value="lunas">Lunas</option>
                        <option value="belum_lunas">Belum Lunas</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Tanggal</th>
                            <th>PlayBox</th>
                            <th>Tipe</th>
                            <th>Jam</th>
                            <th>Durasi</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rentals as $r)
                            <tr>
                                <td class="fw-semibold">{{ $r->invoice_number }}</td>
                                <td>{{ $r->rental_date?->format('d-m-Y') }}</td>
                                <td>{{ $r->playbox?->name }} <span class="text-secondary small">({{ $r->playbox?->code }})</span></td>
                                <td><span class="badge {{ $r->rental_type === 'pribadi' ? 'bg-primary' : 'bg-success' }} text-uppercase">{{ $r->rental_type }}</span></td>
                                <td>{{ \Illuminate\Support\Str::of($r->start_time)->limit(5,'') }} - {{ \Illuminate\Support\Str::of($r->end_time)->limit(5,'') }}</td>
                                <td>{{ rtrim(rtrim(number_format($r->duration,2,'.',''),'0'),'.') }} jam</td>
                                <td>{{ Rupiah::format($r->total_income) }}</td>
                                <td>
                                    <span class="badge {{ $r->payment_status === 'lunas' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $r->payment_status === 'lunas' ? 'Lunas' : 'Belum' }}</span>
                                    <span class="badge bg-light text-dark text-uppercase ms-1">{{ $r->payment_method }}</span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-danger"
                                        onclick="Swal.fire({title:'Hapus transaksi?',text:'Laporan terkait juga akan dihapus.',icon:'warning',showCancelButton:true,confirmButtonColor:'#ef476f',confirmButtonText:'Ya, hapus'}).then(r=>{ if(r.isConfirmed) @this.call('delete', {{ $r->id }}); })">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-secondary py-4">Belum ada transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-2">{{ $rentals->links() }}</div>
        </div>
    </div>

    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.45);">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Transaksi Rental Baru</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">PlayBox <span class="text-danger">*</span></label>
                                    <select class="form-select @error('playbox_id') is-invalid @enderror" wire:model.live="playbox_id">
                                        <option value="">- Pilih PlayBox -</option>
                                        @foreach($playboxes as $pb)
                                            <option value="{{ $pb->id }}">[{{ $pb->code }}] {{ $pb->name }} - {{ ucfirst($pb->ownership_type) }} ({{ ucfirst(str_replace('_',' ', $pb->status)) }})</option>
                                        @endforeach
                                    </select>
                                    @error('playbox_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tipe Transaksi</label>
                                    <select class="form-select" wire:model.live="rental_type">
                                        <option value="pribadi">Pribadi</option>
                                        <option value="kerjasama">Kerjasama</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Mitra/Cafe</label>
                                    <select class="form-select @error('partner_id') is-invalid @enderror" wire:model.defer="partner_id" {{ $rental_type === 'pribadi' ? 'disabled' : '' }}>
                                        <option value="">- Pilih Mitra -</option>
                                        @foreach($partners as $pt)
                                            <option value="{{ $pt->id }}">{{ $pt->cafe_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('partner_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" wire:model.live="rental_date">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Jam Mulai</label>
                                    <input type="time" class="form-control @error('start_time') is-invalid @enderror" wire:model.live="start_time">
                                    @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Jam Selesai</label>
                                    <input type="time" class="form-control @error('end_time') is-invalid @enderror" wire:model.live="end_time">
                                    @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tarif/Jam (Rp)</label>
                                    <input type="number" min="0" step="500" class="form-control @error('price_per_hour') is-invalid @enderror" wire:model.live.debounce.400ms="price_per_hour">
                                    @error('price_per_hour') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Metode Pembayaran</label>
                                    <select class="form-select" wire:model.defer="payment_method">
                                        <option value="cash">Cash</option>
                                        <option value="transfer">Transfer</option>
                                        <option value="qris">QRIS</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status Pembayaran</label>
                                    <select class="form-select" wire:model.defer="payment_status">
                                        <option value="lunas">Lunas</option>
                                        <option value="belum_lunas">Belum Lunas</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nama Customer</label>
                                    <input type="text" class="form-control" wire:model.defer="customer_name" placeholder="(opsional)">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Catatan</label>
                                    <textarea class="form-control" rows="2" wire:model.defer="note"></textarea>
                                </div>
                            </div>

                            <div class="alert alert-light border mt-3 mb-0">
                                <div class="row align-items-center">
                                    <div class="col-md-3"><div class="text-secondary small">Durasi</div><div class="fw-bold">{{ $this->duration }} jam</div></div>
                                    <div class="col-md-3"><div class="text-secondary small">Total Pendapatan</div><div class="fw-bold text-primary">{{ Rupiah::format($this->totalIncome) }}</div></div>
                                    @if($preview['mode'] === 'pribadi')
                                        <div class="col-md-3"><div class="text-secondary small">Maintenance (20%)</div><div class="fw-bold text-danger">{{ Rupiah::format($preview['maintenance']) }}</div></div>
                                        <div class="col-md-3"><div class="text-secondary small">Keuntungan Owner (80%)</div><div class="fw-bold text-success">{{ Rupiah::format($preview['owner_profit']) }}</div></div>
                                    @else
                                        <div class="col-md-3"><div class="text-secondary small">Biaya Staff</div><div class="fw-bold text-warning">{{ Rupiah::format($preview['staff_cost']) }}</div></div>
                                        <div class="col-md-3"><div class="text-secondary small">Bagi Hasil (50/50)</div>
                                            <div class="fw-bold">Owner: {{ Rupiah::format($preview['owner_share']) }}</div>
                                            <div class="fw-bold">Cafe: {{ Rupiah::format($preview['partner_share']) }}</div>
                                        </div>
                                        @if(!$preview['sufficient'])
                                            <div class="col-12 mt-2">
                                                <div class="alert alert-warning mb-0 small"><i class="bi bi-exclamation-triangle"></i> Pendapatan belum mencukupi biaya staff Rp800.000. Net income otomatis = 0.</div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Batal</button>
                            <button type="submit" class="btn btn-pb-primary">
                                <span wire:loading.remove wire:target="save"><i class="bi bi-save"></i> Simpan Transaksi</span>
                                <span wire:loading wire:target="save"><i class="bi bi-arrow-clockwise"></i> Memproses...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
