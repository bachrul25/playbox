<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Pribadi</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
    h2 { color: #0d3b66; margin: 0 0 4px; }
    .small { font-size: 10px; color: #666; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
    th { background: #0d3b66; color: #fff; }
    .summary { margin-top: 8px; }
    .summary td { padding: 5px 8px; }
    .text-end { text-align: right; }
</style>
</head>
<body>
    <h2>Laporan Pribadi PlayBox Rental</h2>
    <div class="small">
        Periode: {{ $from ?: '-' }} s.d. {{ $to ?: '-' }} | Dicetak: {{ now()->format('d-m-Y H:i') }}
    </div>

    <table class="summary">
        <tr>
            <td><strong>Total Pendapatan</strong></td>
            <td class="text-end">Rp{{ number_format($summary['total_income'],0,',','.') }}</td>
            <td><strong>Maintenance (20%)</strong></td>
            <td class="text-end">Rp{{ number_format($summary['maintenance'],0,',','.') }}</td>
        </tr>
        <tr>
            <td><strong>Keuntungan Owner (80%)</strong></td>
            <td class="text-end">Rp{{ number_format($summary['owner_profit'],0,',','.') }}</td>
            <td><strong>Jumlah Transaksi</strong></td>
            <td class="text-end">{{ number_format($summary['count']) }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Invoice</th>
                <th>PlayBox</th>
                <th class="text-end">Total</th>
                <th class="text-end">Maintenance</th>
                <th class="text-end">Keuntungan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $r)
                <tr>
                    <td>{{ $r->report_date?->format('d-m-Y') }}</td>
                    <td>{{ $r->rental?->invoice_number }}</td>
                    <td>{{ $r->rental?->playbox?->name }}</td>
                    <td class="text-end">Rp{{ number_format($r->total_income,0,',','.') }}</td>
                    <td class="text-end">Rp{{ number_format($r->maintenance_amount,0,',','.') }}</td>
                    <td class="text-end">Rp{{ number_format($r->owner_profit,0,',','.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#888;">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
