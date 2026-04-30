@php use App\Helpers\FormatHelper as F; @endphp
<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="module-tag module-pos">POS</span>
            <h4 class="fw-bold mb-0 mt-2">Riwayat Transaksi</h4>
            <small class="text-muted">Daftar transaksi penjualan POS.</small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-danger" wire:click="exportPdf"><i class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
            <button class="btn btn-outline-success" wire:click="exportExcel"><i class="bi bi-file-earmark-excel me-1"></i> Excel</button>
        </div>
    </div>

    <div class="card card-summary p-3">
        <div class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" class="form-control" placeholder="Cari invoice..." wire:model.live.debounce.300ms="search">
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control" wire:model.live="startDate">
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control" wire:model.live="endDate">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Tanggal</th>
                        <th>Kasir</th>
                        <th>Item</th>
                        <th>Metode</th>
                        <th>Total</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                    <tr>
                        <td class="fw-semibold"><i class="bi bi-receipt text-success me-1"></i> {{ $t->invoice_number }}</td>
                        <td>{{ $t->transaction_date?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td>{{ $t->user->name ?? '-' }}</td>
                        <td>{{ $t->details->sum('quantity') }} item</td>
                        <td><span class="badge bg-info text-dark">{{ $t->payment_method }}</span></td>
                        <td class="fw-bold text-success">{{ F::rupiah($t->total) }}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" wire:click="viewDetail({{ $t->id }})"><i class="bi bi-eye"></i> Detail</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $transactions->links() }}</div>
    </div>

    @if($detail)
    <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">{{ $detail->invoice_number }}</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeDetail"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Tanggal</span><span>{{ $detail->transaction_date?->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted small">Kasir</span><span>{{ $detail->user->name ?? '-' }}</span></div>
                    <div class="d-flex justify-content-between mb-3"><span class="text-muted small">Metode</span><span>{{ $detail->payment_method }}</span></div>
                    <table class="table table-sm">
                        <thead><tr><th>Produk</th><th>Qty</th><th>Harga</th><th class="text-end">Subtotal</th></tr></thead>
                        <tbody>
                            @foreach($detail->details as $d)
                            <tr>
                                <td>{{ $d->product->name ?? '-' }}</td>
                                <td>{{ $d->quantity }}</td>
                                <td>{{ F::rupiah($d->price) }}</td>
                                <td class="text-end">{{ F::rupiah($d->subtotal) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between fw-bold fs-5"><span>Total</span><span class="text-success">{{ F::rupiah($detail->total) }}</span></div>
                    <div class="d-flex justify-content-between"><span>Bayar</span><span>{{ F::rupiah($detail->paid_amount) }}</span></div>
                    <div class="d-flex justify-content-between"><span>Kembali</span><span>{{ F::rupiah($detail->change_amount) }}</span></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" wire:click="closeDetail">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
