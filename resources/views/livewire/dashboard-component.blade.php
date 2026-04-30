@php
    use App\Support\Rupiah;
@endphp
<div>
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
        <div>
            <h4 class="pb-section-title mb-0">Dashboard PlayBox Rental</h4>
            <small class="text-secondary">Ringkasan operasional rental pribadi & kerjasama.</small>
        </div>
        <div class="text-secondary small">
            <i class="bi bi-calendar3"></i> {{ now()->translatedFormat('l, d F Y') }}
        </div>
    </div>

    <div class="row g-3">
        @php
            $cards = [
                ['Pendapatan Hari Ini', $todayIncome, 'bi-cash-stack', '#0d3b66', '#dde7f3'],
                ['Pendapatan Bulan Ini', $monthIncome, 'bi-graph-up-arrow', '#06a77d', '#d4f1e8'],
                ['Total Transaksi', $totalRentals, 'bi-receipt', '#f0a500', '#fcecc3', false],
                ['PlayBox Aktif', $totalPlayboxes, 'bi-controller', '#7e57c2', '#e6d8f7', false],
                ['Mitra/Cafe Aktif', $totalPartners, 'bi-shop', '#ef476f', '#fcd5e1', false],
                ['Pendapatan Pribadi', $privateIncome, 'bi-person-badge', '#0d3b66', '#dde7f3'],
                ['Pendapatan Kerjasama', $partnershipIncome, 'bi-people', '#06a77d', '#d4f1e8'],
                ['Total Maintenance', $totalMaintenance, 'bi-tools', '#ef476f', '#fcd5e1'],
                ['Keuntungan Owner', $ownerProfit, 'bi-piggy-bank', '#06a77d', '#d4f1e8'],
                ['Total Biaya', $totalExpenses, 'bi-wallet2', '#f0a500', '#fcecc3'],
            ];
        @endphp

        @foreach($cards as $card)
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card pb-card-stat h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="icon-wrap" style="background: {{ $card[4] }}; color: {{ $card[3] }};">
                            <i class="bi {{ $card[2] }}"></i>
                        </div>
                        <div>
                            <div class="text-secondary small text-uppercase fw-semibold" style="letter-spacing:.5px;">{{ $card[0] }}</div>
                            <div class="fs-5 fw-bold">
                                @if(isset($card[5]) && $card[5] === false)
                                    {{ number_format($card[1]) }}
                                @else
                                    {{ Rupiah::format($card[1]) }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 fw-bold">Grafik Pendapatan Bulanan {{ $currentYear }}</h6>
                        <span class="badge bg-light text-dark">Total Rental</span>
                    </div>
                    <canvas id="monthlyIncomeChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="mb-3 fw-bold">Pendapatan Pribadi vs Kerjasama</h6>
                    <canvas id="comparisonChart" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Transaksi Terbaru</h6>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Tanggal</th>
                            <th>PlayBox</th>
                            <th>Tipe</th>
                            <th>Total</th>
                            <th>Status Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRentals as $r)
                            <tr>
                                <td class="fw-semibold">{{ $r->invoice_number }}</td>
                                <td>{{ $r->rental_date?->format('d-m-Y') }}</td>
                                <td>{{ $r->playbox?->name }} <span class="text-secondary small">({{ $r->playbox?->code }})</span></td>
                                <td>
                                    <span class="badge {{ $r->rental_type === 'pribadi' ? 'bg-primary' : 'bg-success' }} text-uppercase">{{ $r->rental_type }}</span>
                                </td>
                                <td>{{ Rupiah::format($r->total_income) }}</td>
                                <td>
                                    <span class="badge {{ $r->payment_status === 'lunas' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $r->payment_status === 'lunas' ? 'Lunas' : 'Belum Lunas' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')@endpush
    <script>
        (function(){
            const monthly = @json($monthlyChart);
            const comparison = @json($comparisonChart);

            const initCharts = () => {
                const ctx1 = document.getElementById('monthlyIncomeChart');
                if (ctx1) {
                    if (window._pbMonthly) window._pbMonthly.destroy();
                    window._pbMonthly = new Chart(ctx1, {
                        type: 'bar',
                        data: {
                            labels: monthly.labels,
                            datasets: [{
                                label: 'Pendapatan',
                                data: monthly.data,
                                backgroundColor: '#0d3b66',
                                borderRadius: 6,
                            }]
                        },
                        options: { responsive: true, plugins: { legend: { display: false } },
                            scales: { y: { ticks: { callback: v => 'Rp' + new Intl.NumberFormat('id-ID').format(v) } } } }
                    });
                }
                const ctx2 = document.getElementById('comparisonChart');
                if (ctx2) {
                    if (window._pbCompare) window._pbCompare.destroy();
                    window._pbCompare = new Chart(ctx2, {
                        type: 'doughnut',
                        data: {
                            labels: comparison.labels,
                            datasets: [{ data: [comparison.pribadi, comparison.kerjasama], backgroundColor: ['#0d3b66', '#06a77d'] }]
                        },
                        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
                    });
                }
            };
            if (window.Chart) { initCharts(); }
            else { document.addEventListener('DOMContentLoaded', initCharts); }
        })();
    </script>
</div>
