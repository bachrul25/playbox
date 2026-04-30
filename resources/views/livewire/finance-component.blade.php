@php use App\Helpers\FormatHelper as F; @endphp
<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="module-tag module-finance">Keuangan</span>
            <h4 class="fw-bold mb-0 mt-2">Ringkasan Keuangan</h4>
            <small class="text-muted">Pemasukan, pengeluaran, dan laba/rugi.</small>
        </div>
    </div>

    <div class="card card-summary p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label small">Dari Tanggal</label><input type="date" class="form-control" wire:model.live="startDate"></div>
            <div class="col-md-3"><label class="form-label small">Sampai Tanggal</label><input type="date" class="form-control" wire:model.live="endDate"></div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card card-summary p-3 border-start border-success border-4 h-100">
                <small class="text-muted">Total Pemasukan</small>
                <h4 class="fw-bold text-success mb-0">{{ F::rupiah($totalIncome) }}</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-summary p-3 border-start border-danger border-4 h-100">
                <small class="text-muted">Total Pengeluaran</small>
                <h4 class="fw-bold text-danger mb-0">{{ F::rupiah($totalExpense) }}</h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-summary p-3 border-start border-{{ $profit >= 0 ? 'primary' : 'warning' }} border-4 h-100">
                <small class="text-muted">{{ $profit >= 0 ? 'Laba' : 'Rugi' }}</small>
                <h4 class="fw-bold text-{{ $profit >= 0 ? 'primary' : 'warning' }} mb-0">{{ F::rupiah(abs($profit)) }}</h4>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="card card-summary p-3 h-100">
                <h6 class="fw-bold mb-2"><i class="bi bi-graph-up-arrow me-1"></i> Arus Kas Harian</h6>
                <canvas id="cashlineChart" height="120"></canvas>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card card-summary p-3 h-100">
                <h6 class="fw-bold mb-2"><i class="bi bi-pie-chart me-1"></i> Pemasukan per Sumber</h6>
                <canvas id="incomeChart" height="180"></canvas>
                <ul class="list-unstyled small mt-2 mb-0">
                    @foreach($incomeBySource as $src => $val)
                    <li class="d-flex justify-content-between"><span>{{ ucfirst(str_replace('_',' ',$src)) }}</span><strong class="text-success">{{ F::rupiah($val) }}</strong></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="card card-summary p-3">
        <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart me-1"></i> Pengeluaran per Kategori</h6>
        @if($expenseByCategory->isEmpty())
            <div class="text-muted small">Belum ada pengeluaran pada periode ini.</div>
        @else
            @foreach($expenseByCategory as $cat => $val)
                @php $pct = $totalExpense > 0 ? round(($val / $totalExpense) * 100) : 0; @endphp
                <div class="mb-2">
                    <div class="d-flex justify-content-between small"><span>{{ $cat }}</span><strong>{{ F::rupiah($val) }} ({{ $pct }}%)</strong></div>
                    <div class="progress" style="height:8px;"><div class="progress-bar bg-danger" style="width: {{ $pct }}%"></div></div>
                </div>
            @endforeach
        @endif
    </div>

    @push('scripts')
    <script>
        const labels = @json($labels);
        const inSeries = @json($inSeries);
        const outSeries = @json($outSeries);
        const incomeBySource = @json($incomeBySource);

        new Chart(document.getElementById('cashlineChart'), {
            type: 'line',
            data: {
                labels,
                datasets: [
                    { label: 'Masuk', data: inSeries, borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,.15)', fill: true, tension: .3 },
                    { label: 'Keluar', data: outSeries, borderColor: '#dc2626', backgroundColor: 'rgba(220,38,38,.15)', fill: true, tension: .3 },
                ],
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } },
        });

        new Chart(document.getElementById('incomeChart'), {
            type: 'pie',
            data: {
                labels: Object.keys(incomeBySource),
                datasets: [{ data: Object.values(incomeBySource),
                    backgroundColor: ['#16a34a', '#2563eb', '#f59e0b', '#7c3aed', '#dc2626'] }],
            },
            options: { plugins: { legend: { position: 'bottom' } } },
        });
    </script>
    @endpush
</div>
