<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Laporan Transaksi</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th, td { border: 1px solid #ccc; padding: 5px 7px; }
    th { background: #f3f4f6; text-align: left; }
    h2 { margin: 0; }
</style>
</head>
<body>
    <h2>Laporan Transaksi POS</h2>
    <small>Periode: {{ $startDate ?: 'Semua' }} s/d {{ $endDate ?: 'Semua' }} · Dicetak {{ now()->format('d/m/Y H:i') }}</small>
    <table>
        <thead><tr>
            <th>Invoice</th><th>Tanggal</th><th>Kasir</th><th>Item</th><th>Metode</th><th>Total</th>
        </tr></thead>
        <tbody>
        @php $grandTotal = 0; @endphp
        @foreach($rows as $r)
            @php $grandTotal += $r->total; @endphp
            <tr>
                <td>{{ $r->invoice_number }}</td>
                <td>{{ optional($r->transaction_date)->format('d/m/Y H:i') }}</td>
                <td>{{ $r->user->name ?? '-' }}</td>
                <td>{{ $r->details->sum('quantity') }}</td>
                <td>{{ $r->payment_method }}</td>
                <td>Rp {{ number_format($r->total, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        <tr><th colspan="5" style="text-align:right">TOTAL</th><th>Rp {{ number_format($grandTotal, 0, ',', '.') }}</th></tr>
        </tbody>
    </table>
</body></html>
