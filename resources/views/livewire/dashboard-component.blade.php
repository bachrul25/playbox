@php use App\Helpers\FormatHelper as F; @endphp
<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1">Selamat datang, {{ auth()->user()->name }} 👋</h4>
            <p class="text-muted mb-0">Ringkasan aktivitas dan performa bisnis hari ini.</p>
        </div>
        <span class="badge bg-light text-dark border">{{ now()->translatedFormat('l, d F Y') }}</span>
    </div>

    {{-- Summary cards --}}
    <div class="row g-3 mb-3">
        <div class="col-md-6 col-xl-3">
            <div class="card card-summary p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Pendapatan Hari Ini</div>
                        <h5 class="fw-bold mb-0 mt-1">{{ F::rupiah($revenueToday) }}</h5>
                        <small class="text-muted">POS {{ F::rupiah($posToday) }} · Rental {{ F::rupiah($rentalToday) }}</small>
                    </div>
                    <div class="icon-box bg-success-subtle text-success"><i class="bi bi-cash-stack"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card card-summary p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Pendapatan Bulan Ini</div>
                        <h5 class="fw-bold mb-0 mt-1">{{ F::rupiah($revenueMonth) }}</h5>
                        <small class="text-muted">POS + Rental</small>
                    </div>
                    <div class="icon-box bg-primary-subtle text-primary"><i class="bi bi-calendar-month"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card card-summary p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Transaksi POS Hari Ini</div>
                        <h5 class="fw-bold mb-0 mt-1">{{ $posCountToday }} transaksi</h5>
                        <small class="text-muted">Total {{ F::rupiah($posToday) }}</small>
                    </div>
                    <div class="icon-box bg-warning-subtle text-warning"><i class="bi bi-receipt"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card card-summary p-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Rental Aktif</div>
                        <h5 class="fw-bold mb-0 mt-1">{{ $activeRentals }} unit</h5>
                        <small class="text-muted">{{ $rentalCountToday }} sesi hari ini</small>
                    </div>
                    <div class="icon-box bg-info-subtle text-info"><i class="bi bi-controller"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card card-summary p-3 h-100 border-start border-success border-4">
                <small class="text-muted">Total Pemasukan</small>
                <h4 class="fw-bold text-success mb-0">{{ F::rupiah($totalIncome) }}</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-summary p-3 h-100 border-start border-danger border-4">
                <small class="text-muted">Total Pengeluaran</small>
                <h4 class="fw-bold text-danger mb-0">{{ F::rupiah($totalExpense) }}</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-summary p-3 h-100 border-start border-{{ $profit >= 0 ? 'primary' : 'warning' }} border-4">
                <small class="text-muted">{{ $profit >= 0 ? 'Laba' : 'Rugi' }}</small>
                <h4 class="fw-bold text-{{ $profit >= 0 ? 'primary' : 'warning' }} mb-0">{{ F::rupiah(abs($profit)) }}</h4>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="card card-summary p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0"><i class="bi bi-graph-up me-1"></i> Pendapatan 7 Hari Terakhir</h6>
                    <span class="text-muted small">POS vs Rental</span>
                </div>
                <canvas id="revenueChart" height="120"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-summary p-3 h-100">
                <h6 class="fw-bold mb-2"><i class="bi bi-pie-chart me-1"></i> Status Unit Rental</h6>
                <canvas id="unitChart" height="180"></canvas>
                <div class="mt-3 small">
                    <div><span class="badge bg-success me-1">●</span>Tersedia: {{ $unitStats['available'] }}</div>
                    <div><span class="badge bg-primary me-1">●</span>Disewa: {{ $unitStats['in_use'] }}</div>
                    <div><span class="badge bg-warning me-1">●</span>Maintenance: {{ $unitStats['maintenance'] }}</div>
                    <div><span class="badge bg-secondary me-1">●</span>Nonaktif: {{ $unitStats['inactive'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="card card-summary p-3 h-100">
                <h6 class="fw-bold mb-2"><i class="bi bi-bar-chart me-1"></i> Arus Kas 7 Hari Terakhir</h6>
                <canvas id="cashflowChart" height="110"></canvas>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card card-summary p-3 h-100">
                <h6 class="fw-bold mb-2"><i class="bi bi-trophy me-1"></i> Produk Terlaris</h6>
                <ul class="list-unstyled mb-0">
                    @forelse($bestSelling as $row)
                    <li class="d-flex justify-content-between border-bottom py-2">
                        <span><i class="bi bi-box-seam text-success me-1"></i> {{ $row->product->name ?? '-' }}</span>
                        <span class="badge bg-success-subtle text-success">{{ $row->total_qty }} terjual</span>
                    </li>
                    @empty
                    <li class="text-muted small">Belum ada penjualan.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- Active rentals & low stock --}}
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card card-summary p-3 h-100">
                <h6 class="fw-bold mb-3"><i class="bi bi-controller me-1"></i> Rental Sedang Berjalan</h6>
                @if($activeRentalList->isEmpty())
                    <div class="text-muted small">Tidak ada rental aktif saat ini.</div>
                @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr>
                            <th>Unit</th><th>Pelanggan</th><th>Mulai</th><th>Tarif/Jam</th><th>Status</th>
                        </tr></thead>
                        <tbody>
                        @foreach($activeRentalList as $r)
                            <tr>
                                <td><strong>{{ $r->unit->code ?? '-' }}</strong> · {{ $r->unit->name ?? '-' }}</td>
                                <td>{{ $r->customer_name }}</td>
                                <td>{{ $r->start_time->format('d/m H:i') }}</td>
                                <td>{{ F::rupiah($r->hourly_price) }}</td>
                                <td><span class="badge bg-primary">Aktif</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card card-summary p-3 h-100">
                <h6 class="fw-bold mb-3"><i class="bi bi-exclamation-triangle text-warning me-1"></i> Stok Menipis</h6>
                @if($lowStock->isEmpty())
                    <div class="text-muted small">Semua produk masih aman.</div>
                @else
                    @foreach($lowStock as $p)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span><i class="bi bi-box text-warning me-1"></i> {{ $p->name }}</span>
                            <span class="badge {{ $p->stock == 0 ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $p->stock }}/{{ $p->minimum_stock }}</span>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const labels = @json($chartLabels);
        const posData = @json($chartPos);
        const rentalData = @json($chartRental);
        const cashIn = @json($chartCashIn);
        const cashOut = @json($chartCashOut);
        const unitStats = @json($unitStats);

        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels,
                datasets: [
                    { label: 'POS', data: posData, borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,.15)', fill: true, tension: .35 },
                    { label: 'Rental', data: rentalData, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,.15)', fill: true, tension: .35 },
                ],
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } },
        });

        new Chart(document.getElementById('unitChart'), {
            type: 'doughnut',
            data: {
                labels: ['Tersedia','Disewa','Maintenance','Nonaktif'],
                datasets: [{ data: [unitStats.available, unitStats.in_use, unitStats.maintenance, unitStats.inactive],
                    backgroundColor: ['#16a34a','#2563eb','#f59e0b','#6b7280'] }],
            },
            options: { plugins: { legend: { display: false } } },
        });

        new Chart(document.getElementById('cashflowChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { label: 'Masuk', data: cashIn, backgroundColor: '#16a34a' },
                    { label: 'Keluar', data: cashOut, backgroundColor: '#dc2626' },
                ],
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } },
        });
    </script>
    @endpush
</div>
