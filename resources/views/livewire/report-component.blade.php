@php use App\Helpers\FormatHelper as F; @endphp
<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="module-tag module-report">Laporan</span>
            <h4 class="fw-bold mb-0 mt-2">Laporan & Analytics</h4>
            <small class="text-muted">Analisis lintas modul (POS, Rental, Keuangan).</small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-danger" wire:click="exportPdf"><i class="bi bi-file-earmark-pdf me-1"></i> Export PDF</button>
            <button class="btn btn-outline-success" wire:click="exportExcel"><i class="bi bi-file-earmark-excel me-1"></i> Export Excel</button>
            <button class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print</button>
        </div>
    </div>

    <div class="card card-summary p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label small">Dari</label><input type="date" class="form-control" wire:model.live="startDate"></div>
            <div class="col-md-3"><label class="form-label small">Sampai</label><input type="date" class="form-control" wire:model.live="endDate"></div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="card card-summary p-3 border-start border-success border-4"><small class="text-muted">Penjualan POS</small><h5 class="fw-bold text-success mb-0">{{ F::rupiah($totalPos) }}</h5></div></div>
        <div class="col-md-3"><div class="card card-summary p-3 border-start border-primary border-4"><small class="text-muted">Pendapatan Rental</small><h5 class="fw-bold text-primary mb-0">{{ F::rupiah($totalRental) }}</h5></div></div>
        <div class="col-md-3"><div class="card card-summary p-3 border-start border-info border-4"><small class="text-muted">Total Pemasukan</small><h5 class="fw-bold text-info mb-0">{{ F::rupiah($totalIncome) }}</h5></div></div>
        <div class="col-md-3"><div class="card card-summary p-3 border-start border-{{ $profit >= 0 ? 'warning' : 'danger' }} border-4"><small class="text-muted">{{ $profit >= 0 ? 'Laba' : 'Rugi' }}</small><h5 class="fw-bold text-{{ $profit >= 0 ? 'warning' : 'danger' }} mb-0">{{ F::rupiah(abs($profit)) }}</h5></div></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="card card-summary p-3 h-100">
                <h6 class="fw-bold mb-2"><i class="bi bi-graph-up me-1"></i> Pendapatan: POS vs Rental</h6>
                <canvas id="reportRevenueChart" height="120"></canvas>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card card-summary p-3 h-100">
                <h6 class="fw-bold mb-2"><i class="bi bi-clock me-1"></i> Jam Ramai Rental</h6>
                <canvas id="busyHoursChart" height="180"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card card-summary p-3 h-100">
                <h6 class="fw-bold mb-2"><i class="bi bi-trophy me-1"></i> Produk Terlaris</h6>
                <table class="table table-sm">
                    <thead><tr><th>Produk</th><th class="text-end">Qty</th><th class="text-end">Pendapatan</th></tr></thead>
                    <tbody>
                    @forelse($bestSelling as $row)
                        <tr><td>{{ $row->product->name ?? '-' }}</td><td class="text-end">{{ $row->total_qty }}</td><td class="text-end">{{ F::rupiah($row->total_revenue) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-muted text-center small">Belum ada data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card card-summary p-3 h-100">
                <h6 class="fw-bold mb-2"><i class="bi bi-controller me-1"></i> Unit Rental Terpopuler</h6>
                <table class="table table-sm">
                    <thead><tr><th>Unit</th><th class="text-end">Sesi</th><th class="text-end">Pendapatan</th></tr></thead>
                    <tbody>
                    @forelse($popularUnits as $row)
                        <tr><td>{{ $row['name'] }}</td><td class="text-end">{{ $row['sessions'] }}</td><td class="text-end">{{ F::rupiah($row['total']) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-muted text-center small">Belum ada data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card card-summary p-3 h-100">
                <h6 class="fw-bold mb-2"><i class="bi bi-graph-down me-1"></i> Pengeluaran Terbesar</h6>
                <table class="table table-sm">
                    <thead><tr><th>Tanggal</th><th>Kategori</th><th>Deskripsi</th><th class="text-end">Jumlah</th></tr></thead>
                    <tbody>
                    @forelse($bigExpenses as $e)
                        <tr><td>{{ $e->date->format('d/m') }}</td><td>{{ $e->category->name ?? '-' }}</td><td>{{ \Illuminate\Support\Str::limit($e->description, 30) }}</td><td class="text-end text-danger">{{ F::rupiah($e->amount) }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center small">Belum ada pengeluaran.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card card-summary p-3 h-100">
                <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle text-warning me-1"></i> Stok Menipis</h6>
                <table class="table table-sm">
                    <thead><tr><th>Produk</th><th class="text-end">Stok</th><th class="text-end">Min.</th></tr></thead>
                    <tbody>
                    @forelse($lowStock as $p)
                        <tr><td>{{ $p->name }}</td><td class="text-end">{{ $p->stock }}</td><td class="text-end">{{ $p->minimum_stock }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-muted text-center small">Semua stok aman.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const labels = @json($labels);
        const posSeries = @json($posSeries);
        const rentalSeries = @json($rentalSeries);
        const busyHours = @json($busyHours);

        new Chart(document.getElementById('reportRevenueChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { label: 'POS', data: posSeries, backgroundColor: '#16a34a' },
                    { label: 'Rental', data: rentalSeries, backgroundColor: '#2563eb' },
                ],
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } },
        });

        new Chart(document.getElementById('busyHoursChart'), {
            type: 'line',
            data: {
                labels: Object.keys(busyHours),
                datasets: [{ label: 'Jumlah Sesi', data: Object.values(busyHours), borderColor: '#7c3aed', backgroundColor: 'rgba(124,58,237,.15)', fill: true, tension: .3 }],
            },
            options: { responsive: true, plugins: { legend: { display: false } } },
        });
    </script>
    @endpush
</div>
