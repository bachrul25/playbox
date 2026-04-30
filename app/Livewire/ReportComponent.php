<?php

namespace App\Livewire;

use App\Models\Cashflow;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Product;
use App\Models\Rental;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Laporan & Analytics')]
#[Layout('layouts.app')]
class ReportComponent extends Component
{
    #[Url(history: true)]
    public string $startDate = '';

    #[Url(history: true)]
    public string $endDate = '';

    public function mount(): void
    {
        if (! $this->startDate) {
            $this->startDate = now()->startOfMonth()->toDateString();
        }
        if (! $this->endDate) {
            $this->endDate = now()->toDateString();
        }
    }

    private function range(): array
    {
        return [Carbon::parse($this->startDate)->startOfDay(), Carbon::parse($this->endDate)->endOfDay()];
    }

    public function exportPdf()
    {
        $data = $this->buildData();
        $pdf = Pdf::loadView('exports.report-pdf', $data + ['startDate' => $this->startDate, 'endDate' => $this->endDate]);

        return response()->streamDownload(fn () => print ($pdf->output()), 'laporan-'.now()->format('Ymd-His').'.pdf');
    }

    public function exportExcel()
    {
        $data = $this->buildData();

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Laporan Periode', $this->startDate.' s/d '.$this->endDate]);
            fputcsv($out, []);
            fputcsv($out, ['== RINGKASAN ==']);
            fputcsv($out, ['Total Pemasukan', $data['totalIncome']]);
            fputcsv($out, ['Total Pengeluaran', $data['totalExpense']]);
            fputcsv($out, ['Laba/Rugi', $data['profit']]);
            fputcsv($out, ['Total Penjualan POS', $data['totalPos']]);
            fputcsv($out, ['Total Rental', $data['totalRental']]);
            fputcsv($out, []);
            fputcsv($out, ['== PRODUK TERLARIS ==']);
            fputcsv($out, ['Produk', 'Qty Terjual', 'Total Pendapatan']);
            foreach ($data['bestSelling'] as $row) {
                fputcsv($out, [$row->product->name ?? '-', $row->total_qty, $row->total_revenue]);
            }
            fputcsv($out, []);
            fputcsv($out, ['== UNIT RENTAL TERPOPULER ==']);
            fputcsv($out, ['Unit', 'Sesi', 'Total Pendapatan']);
            foreach ($data['popularUnits'] as $row) {
                fputcsv($out, [$row['name'] ?? '-', $row['sessions'], $row['total']]);
            }
            fclose($out);
        }, 'laporan-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function buildData(): array
    {
        [$start, $end] = $this->range();

        $totalPos = Transaction::whereBetween('transaction_date', [$start, $end])->sum('total');
        $totalRental = Rental::where('status', 'finished')->whereBetween('updated_at', [$start, $end])->sum('total_price');
        $totalIncome = Income::whereBetween('date', [$start, $end])->sum('amount');
        $totalExpense = Expense::whereBetween('date', [$start, $end])->sum('amount');
        $profit = $totalIncome - $totalExpense;

        $bestSelling = TransactionDetail::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->whereHas('transaction', fn ($q) => $q->whereBetween('transaction_date', [$start, $end]))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product:id,name')
            ->take(10)->get();

        $popularUnits = Rental::with('unit')
            ->where('status', 'finished')
            ->whereBetween('updated_at', [$start, $end])
            ->get()
            ->groupBy('rental_unit_id')
            ->map(fn ($g) => [
                'name' => $g->first()->unit->name ?? 'Unit',
                'sessions' => $g->count(),
                'total' => (float) $g->sum('total_price'),
            ])->sortByDesc('total')->values();

        $busyHours = Rental::whereBetween('start_time', [$start, $end])
            ->get()->groupBy(fn ($r) => $r->start_time->format('H'))
            ->map(fn ($g) => $g->count());
        $busyHours = collect(range(0, 23))->mapWithKeys(fn ($h) => [str_pad((string) $h, 2, '0', STR_PAD_LEFT) => $busyHours[str_pad((string) $h, 2, '0', STR_PAD_LEFT)] ?? 0]);

        $lowStock = Product::whereColumn('stock', '<=', 'minimum_stock')
            ->where('status', 'active')->orderBy('stock')->get();

        $bigExpenses = Expense::with('category')
            ->whereBetween('date', [$start, $end])
            ->orderByDesc('amount')->take(10)->get();

        // Charts data
        $days = collect();
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $days->push($d->copy());
        }
        $labels = $days->map(fn ($d) => $d->format('d/m'))->values();
        $posSeries = $days->map(fn ($d) => (float) Transaction::whereDate('transaction_date', $d)->sum('total'))->values();
        $rentalSeries = $days->map(fn ($d) => (float) Rental::where('status', 'finished')->whereDate('updated_at', $d)->sum('total_price'))->values();
        $cashIn = $days->map(fn ($d) => (float) Cashflow::where('type', 'in')->whereDate('date', $d)->sum('amount'))->values();
        $cashOut = $days->map(fn ($d) => (float) Cashflow::where('type', 'out')->whereDate('date', $d)->sum('amount'))->values();

        return compact('totalPos', 'totalRental', 'totalIncome', 'totalExpense', 'profit', 'bestSelling', 'popularUnits', 'busyHours', 'lowStock', 'bigExpenses', 'labels', 'posSeries', 'rentalSeries', 'cashIn', 'cashOut');
    }

    public function render()
    {
        return view('livewire.report-component', $this->buildData());
    }
}
