@php use App\Helpers\FormatHelper as F; @endphp
<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="module-tag module-finance">Keuangan</span>
            <h4 class="fw-bold mb-0 mt-2">Arus Kas</h4>
            <small class="text-muted">Semua transaksi pemasukan dan pengeluaran.</small>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="card card-summary p-3 border-start border-success border-4"><small class="text-muted">Kas Masuk</small><h5 class="fw-bold text-success mb-0">{{ F::rupiah($totalIn) }}</h5></div></div>
        <div class="col-md-4"><div class="card card-summary p-3 border-start border-danger border-4"><small class="text-muted">Kas Keluar</small><h5 class="fw-bold text-danger mb-0">{{ F::rupiah($totalOut) }}</h5></div></div>
        <div class="col-md-4"><div class="card card-summary p-3 border-start border-{{ $net >= 0 ? 'primary' : 'warning' }} border-4"><small class="text-muted">Saldo Bersih</small><h5 class="fw-bold text-{{ $net >= 0 ? 'primary' : 'warning' }} mb-0">{{ F::rupiah($net) }}</h5></div></div>
    </div>

    <div class="card card-summary p-3">
        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <select class="form-select" wire:model.live="filterType">
                    <option value="">Semua tipe</option>
                    <option value="in">Masuk</option>
                    <option value="out">Keluar</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" wire:model.live="filterSource">
                    <option value="">Semua sumber</option>
                    <option value="pos">POS</option>
                    <option value="rental">Rental</option>
                    <option value="manual_income">Pemasukan Manual</option>
                    <option value="expense">Pengeluaran</option>
                </select>
            </div>
            <div class="col-md-3"><input type="date" class="form-control" wire:model.live="startDate"></div>
            <div class="col-md-3"><input type="date" class="form-control" wire:model.live="endDate"></div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Tanggal</th><th>Tipe</th><th>Sumber</th><th>Deskripsi</th><th class="text-end">Jumlah</th></tr></thead>
                <tbody>
                @forelse($flows as $f)
                <tr>
                    <td>{{ $f->date->format('d/m/Y') }}</td>
                    <td><span class="badge bg-{{ $f->type === 'in' ? 'success' : 'danger' }}"><i class="bi bi-arrow-{{ $f->type === 'in' ? 'down' : 'up' }}"></i> {{ strtoupper($f->type) }}</span></td>
                    <td><span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_',' ',$f->source)) }}</span></td>
                    <td>{{ $f->description ?? '-' }}</td>
                    <td class="text-end fw-bold text-{{ $f->type === 'in' ? 'success' : 'danger' }}">
                        {{ $f->type === 'in' ? '+' : '-' }} {{ F::rupiah($f->amount) }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data arus kas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $flows->links() }}</div>
    </div>
</div>
