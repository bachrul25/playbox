@php
    use App\Support\Rupiah;
    $isMitra = auth()->user()?->role === 'mitra';
@endphp
<div>
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
        <h4 class="pb-section-title mb-0"><i class="bi bi-file-earmark-spreadsheet"></i> Laporan Kerjasama</h4>
        <div class="btn-group">
            <button class="btn btn-outline-danger" wire:click="exportPdf"><i class="bi bi-file-earmark-pdf"></i> Export PDF</button>
            <button class="btn btn-outline-success" wire:click="exportExcel"><i class="bi bi-file-earmark-excel"></i> Export Excel</button>
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
                <div class="col-6 col-md-2">
                    <input type="date" class="form-control form-control-sm" wire:model.live="from">
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" class="form-control form-control-sm" wire:model.live="to">
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select form-select-sm" wire:model.live="partnerId" {{ $isMitra ? 'disabled' : '' }}>
                        <option value="">Semua Mitra</option>
                        @foreach($partners as $pt)
                            <option value="{{ $pt->id }}">{{ $pt->cafe_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        @php
            $sum = [
                ['Total Pendapatan', $summary['total_income'], '#0d3b66'],
                ['Biaya Staff', $summary['staff_cost'], '#f0a500'],
                ['Sisa Pendapatan Bersih', $summary['net_income'], '#06a77d'],
                ['Bagian Owner (50%)', $summary['owner_share'], '#0d3b66'],
                ['Bagian Cafe (50%)', $summary['partner_share'], '#06a77d'],
            ];
        @endphp
        @foreach($sum as $card)
            <div class="col-12 col-md">
                <div class="card pb-card-stat h-100">
                    <div class="card-body">
                        <div class="text-secondary small text-uppercase">{{ $card[0] }}</div>
                        <div class="fs-6 fw-bold" style="color: {{ $card[2] }};">{{ Rupiah::format($card[1]) }}</div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="col-12 col-md">
            <div class="card pb-card-stat h-100">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase">Jumlah Transaksi</div>
                    <div class="fs-5 fw-bold text-warning">{{ number_format($summary['count']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Invoice</th>
                            <th>Mitra/Cafe</th>
                            <th>PlayBox</th>
                            <th>Total</th>
                            <th>Staff</th>
                            <th>Net</th>
                            <th>Owner 50%</th>
                            <th>Cafe 50%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $r)
                            <tr>
                                <td>{{ $r->report_date?->format('d-m-Y') }}</td>
                                <td>{{ $r->rental?->invoice_number }}</td>
                                <td class="fw-semibold">{{ $r->partner?->cafe_name }}</td>
                                <td>{{ $r->rental?->playbox?->name }}</td>
                                <td>{{ Rupiah::format($r->total_income) }}</td>
                                <td>{{ Rupiah::format($r->staff_cost) }}</td>
                                <td>{{ Rupiah::format($r->net_income) }}</td>
                                <td>{{ Rupiah::format($r->owner_share) }}</td>
                                <td>{{ Rupiah::format($r->partner_share) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-secondary py-4">Tidak ada laporan dalam rentang yang dipilih.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-2">{{ $reports->links() }}</div>
        </div>
    </div>
</div>
