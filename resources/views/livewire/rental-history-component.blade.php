@php use App\Helpers\FormatHelper as F; @endphp
<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="module-tag module-rental">Rental</span>
            <h4 class="fw-bold mb-0 mt-2">Riwayat Rental</h4>
            <small class="text-muted">Daftar semua sesi rental.</small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-danger" wire:click="exportPdf"><i class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
            <button class="btn btn-outline-success" wire:click="exportExcel"><i class="bi bi-file-earmark-excel me-1"></i> Excel</button>
        </div>
    </div>

    <div class="card card-summary p-3">
        <div class="row g-2 mb-3">
            <div class="col-md-3"><input type="text" class="form-control" placeholder="Cari invoice / pelanggan..." wire:model.live.debounce.300ms="search"></div>
            <div class="col-md-2"><input type="date" class="form-control" wire:model.live="startDate"></div>
            <div class="col-md-2"><input type="date" class="form-control" wire:model.live="endDate"></div>
            <div class="col-md-3">
                <select class="form-select" wire:model.live="filterStatus">
                    <option value="">Semua status</option>
                    <option value="active">Aktif</option>
                    <option value="finished">Selesai</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr>
                    <th>Invoice</th><th>Pelanggan</th><th>Unit</th><th>Mulai</th><th>Durasi</th><th>Total</th><th>Status</th><th class="text-end">Aksi</th>
                </tr></thead>
                <tbody>
                @forelse($rentals as $r)
                <tr>
                    <td class="fw-semibold">{{ $r->invoice_number }}</td>
                    <td>{{ $r->customer_name }}</td>
                    <td>{{ $r->unit->code ?? '-' }} · {{ $r->unit->name ?? '-' }}</td>
                    <td>{{ $r->start_time?->format('d/m H:i') }}</td>
                    <td>{{ \App\Helpers\FormatHelper::durationHuman((int)$r->duration_minutes) }}</td>
                    <td class="fw-bold text-primary">{{ F::rupiah($r->total_price) }}</td>
                    <td>
                        @php $colors = ['active' => 'primary', 'finished' => 'success', 'cancelled' => 'secondary']; @endphp
                        <span class="badge bg-{{ $colors[$r->status] ?? 'secondary' }}">{{ ucfirst($r->status) }}</span>
                    </td>
                    <td class="text-end"><button class="btn btn-sm btn-outline-primary" wire:click="viewDetail({{ $r->id }})"><i class="bi bi-eye"></i></button></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data rental.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $rentals->links() }}</div>
    </div>

    @if($detail)
    <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white"><h5 class="modal-title">{{ $detail->invoice_number }}</h5><button type="button" class="btn-close btn-close-white" wire:click="closeDetail"></button></div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between"><span class="text-muted small">Pelanggan</span><span>{{ $detail->customer_name }}</span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted small">Unit</span><span>{{ $detail->unit->name ?? '-' }} ({{ $detail->unit->code ?? '-' }})</span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted small">Mode</span><span>{{ $detail->mode }}</span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted small">Mulai</span><span>{{ $detail->start_time?->format('d/m/Y H:i') }}</span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted small">Selesai</span><span>{{ $detail->end_time?->format('d/m/Y H:i') ?? '-' }}</span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted small">Durasi</span><span>{{ \App\Helpers\FormatHelper::durationHuman((int)$detail->duration_minutes) }}</span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted small">Tarif/Jam</span><span>{{ F::rupiah($detail->hourly_price) }}</span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted small">Metode</span><span>{{ $detail->payment_method }}</span></div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5"><span>Total</span><span class="text-success">{{ F::rupiah($detail->total_price) }}</span></div>
                    @if($detail->sessions->count())
                    <h6 class="mt-3 fw-bold">Sesi</h6>
                    <ul class="list-unstyled small mb-0">
                        @foreach($detail->sessions as $s)
                        <li>· {{ $s->start_time?->format('H:i') }} → {{ $s->end_time?->format('H:i') ?? 'aktif' }} {{ $s->additional_minutes ? "(+{$s->additional_minutes} mnt)" : '' }}</li>
                        @endforeach
                    </ul>
                    @endif
                </div>
                <div class="modal-footer"><button class="btn btn-light" wire:click="closeDetail">Tutup</button></div>
            </div>
        </div>
    </div>
    @endif
</div>
