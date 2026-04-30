@php
    use App\Support\Rupiah;
@endphp
<div>
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
        <h4 class="pb-section-title mb-0"><i class="bi bi-file-earmark-bar-graph"></i> Laporan Pribadi</h4>
        <div class="btn-group">
            <button class="btn btn-outline-danger" wire:click="exportPdf">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </button>
            <button class="btn btn-outline-success" wire:click="exportExcel">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-12 col-md-5">
                    <div class="btn-group" role="group">
                        @foreach(['harian'=>'Harian','mingguan'=>'Mingguan','bulanan'=>'Bulanan','tahunan'=>'Tahunan'] as $key=>$label)
                            <button type="button" class="btn btn-sm {{ $period === $key ? 'btn-pb-primary' : 'btn-outline-primary' }}" wire:click="applyPeriod('{{ $key }}')">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <input type="date" class="form-control form-control-sm" wire:model.live="from" placeholder="Dari">
                </div>
                <div class="col-6 col-md-3">
                    <input type="date" class="form-control form-control-sm" wire:model.live="to" placeholder="Sampai">
                </div>
                <div class="col-12 col-md-1 text-end small text-secondary">
                    {{ $fromResolved ?: '...' }} → {{ $toResolved ?: '...' }}
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        @php
            $sum = [
                ['Total Pendapatan', $summary['total_income'], 'bi-cash-stack', '#0d3b66'],
                ['Maintenance (20%)', $summary['maintenance'], 'bi-tools', '#ef476f'],
                ['Keuntungan Owner (80%)', $summary['owner_profit'], 'bi-piggy-bank', '#06a77d'],
                ['Jumlah Transaksi', $summary['count'], 'bi-receipt', '#f0a500'],
            ];
        @endphp
        @foreach($sum as $i => $card)
            <div class="col-12 col-md-3">
                <div class="card pb-card-stat h-100">
                    <div class="card-body">
                        <div class="text-secondary small text-uppercase">{{ $card[0] }}</div>
                        <div class="fs-5 fw-bold" style="color: {{ $card[3] }};">
                            @if($i === 3) {{ number_format($card[1]) }} @else {{ Rupiah::format($card[1]) }} @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Invoice</th>
                            <th>PlayBox</th>
                            <th>Total Pendapatan</th>
                            <th>Maintenance (20%)</th>
                            <th>Keuntungan (80%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $r)
                            <tr>
                                <td>{{ $r->report_date?->format('d-m-Y') }}</td>
                                <td>{{ $r->rental?->invoice_number }}</td>
                                <td>{{ $r->rental?->playbox?->name }}</td>
                                <td>{{ Rupiah::format($r->total_income) }}</td>
                                <td>{{ Rupiah::format($r->maintenance_amount) }}</td>
                                <td>{{ Rupiah::format($r->owner_profit) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-secondary py-4">Tidak ada laporan dalam rentang yang dipilih.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-2">{{ $reports->links() }}</div>
        </div>
    </div>
</div>
