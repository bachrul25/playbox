<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Laporan</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    th, td { border: 1px solid #ccc; padding: 5px 7px; }
    th { background: #f3f4f6; text-align: left; }
    h2, h4 { margin: 0 0 6px 0; }
    .summary td { background: #f9fafb; }
</style></head>
<body>
    <h2>Laporan Bisnis Terpadu</h2>
    <small>Periode: {{ $startDate }} s/d {{ $endDate }} · Dicetak {{ now()->format('d/m/Y H:i') }}</small>

    <h4 style="margin-top:14px">Ringkasan</h4>
    <table class="summary">
        <tr><td>Penjualan POS</td><td>Rp {{ number_format($totalPos, 0, ',', '.') }}</td></tr>
        <tr><td>Pendapatan Rental</td><td>Rp {{ number_format($totalRental, 0, ',', '.') }}</td></tr>
        <tr><td>Total Pemasukan</td><td>Rp {{ number_format($totalIncome, 0, ',', '.') }}</td></tr>
        <tr><td>Total Pengeluaran</td><td>Rp {{ number_format($totalExpense, 0, ',', '.') }}</td></tr>
        <tr><td><strong>{{ $profit >= 0 ? 'Laba' : 'Rugi' }}</strong></td><td><strong>Rp {{ number_format(abs($profit), 0, ',', '.') }}</strong></td></tr>
    </table>

    <h4>Produk Terlaris</h4>
    <table>
        <thead><tr><th>Produk</th><th>Qty Terjual</th><th>Pendapatan</th></tr></thead>
        <tbody>
        @forelse($bestSelling as $r)
            <tr><td>{{ $r->product->name ?? '-' }}</td><td>{{ $r->total_qty }}</td><td>Rp {{ number_format($r->total_revenue, 0, ',', '.') }}</td></tr>
        @empty
            <tr><td colspan="3">Belum ada data.</td></tr>
        @endforelse
        </tbody>
    </table>

    <h4>Unit Rental Terpopuler</h4>
    <table>
        <thead><tr><th>Unit</th><th>Sesi</th><th>Pendapatan</th></tr></thead>
        <tbody>
        @forelse($popularUnits as $r)
            <tr><td>{{ $r['name'] }}</td><td>{{ $r['sessions'] }}</td><td>Rp {{ number_format($r['total'], 0, ',', '.') }}</td></tr>
        @empty
            <tr><td colspan="3">Belum ada data.</td></tr>
        @endforelse
        </tbody>
    </table>

    <h4>Pengeluaran Terbesar</h4>
    <table>
        <thead><tr><th>Tanggal</th><th>Kategori</th><th>Deskripsi</th><th>Jumlah</th></tr></thead>
        <tbody>
        @forelse($bigExpenses as $e)
            <tr><td>{{ $e->date->format('d/m/Y') }}</td><td>{{ $e->category->name ?? '-' }}</td><td>{{ $e->description }}</td><td>Rp {{ number_format($e->amount, 0, ',', '.') }}</td></tr>
        @empty
            <tr><td colspan="4">Belum ada pengeluaran.</td></tr>
        @endforelse
        </tbody>
    </table>
</body></html>
