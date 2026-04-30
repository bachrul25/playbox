<?php

namespace App\Livewire;

use App\Models\Cashflow;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Product;
use App\Models\Rental;
use App\Models\RentalUnit;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
#[Layout('layouts.app')]
class DashboardComponent extends Component
{
    public function render()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // POS revenue
        $posToday = Transaction::whereDate('transaction_date', $today)->sum('total');
        $posMonth = Transaction::whereDate('transaction_date', '>=', $startOfMonth)->sum('total');
        $posCountToday = Transaction::whereDate('transaction_date', $today)->count();

        // Rental revenue
        $rentalToday = Rental::where('status', 'finished')->whereDate('updated_at', $today)->sum('total_price');
        $rentalMonth = Rental::where('status', 'finished')->whereDate('updated_at', '>=', $startOfMonth)->sum('total_price');
        $rentalCountToday = Rental::whereDate('start_time', $today)->count();
        $activeRentals = Rental::where('status', 'active')->count();

        // Combined finance
        $totalIncome = Income::sum('amount');
        $totalExpense = Expense::sum('amount');
        $profit = $totalIncome - $totalExpense;
        $revenueToday = $posToday + $rentalToday;
        $revenueMonth = $posMonth + $rentalMonth;

        // Best selling products (top 5 by qty)
        $bestSelling = TransactionDetail::select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product:id,name,selling_price')
            ->take(5)
            ->get();

        // Low stock
        $lowStock = Product::whereColumn('stock', '<=', 'minimum_stock')
            ->where('status', 'active')
            ->orderBy('stock')
            ->take(8)
            ->get();

        // Active rentals list
        $activeRentalList = Rental::with('unit')->where('status', 'active')->latest('start_time')->get();

        // Charts: last 7 days revenue
        $days = collect(range(6, 0))->map(fn ($i) => Carbon::today()->subDays($i));
        $dailyLabels = $days->map(fn ($d) => $d->format('d/m'))->values();
        $dailyPos = $days->map(fn ($d) => (float) Transaction::whereDate('transaction_date', $d)->sum('total'))->values();
        $dailyRental = $days->map(fn ($d) => (float) Rental::where('status', 'finished')->whereDate('updated_at', $d)->sum('total_price'))->values();

        // Cashflow last 7 days
        $cashIn = $days->map(fn ($d) => (float) Cashflow::where('type', 'in')->whereDate('date', $d)->sum('amount'))->values();
        $cashOut = $days->map(fn ($d) => (float) Cashflow::where('type', 'out')->whereDate('date', $d)->sum('amount'))->values();

        // Unit availability
        $unitStats = [
            'available' => RentalUnit::where('status', 'available')->count(),
            'in_use' => RentalUnit::where('status', 'in_use')->count(),
            'maintenance' => RentalUnit::where('status', 'maintenance')->count(),
            'inactive' => RentalUnit::where('status', 'inactive')->count(),
        ];

        return view('livewire.dashboard-component', [
            'posToday' => $posToday,
            'posMonth' => $posMonth,
            'posCountToday' => $posCountToday,
            'rentalToday' => $rentalToday,
            'rentalMonth' => $rentalMonth,
            'rentalCountToday' => $rentalCountToday,
            'activeRentals' => $activeRentals,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'profit' => $profit,
            'revenueToday' => $revenueToday,
            'revenueMonth' => $revenueMonth,
            'bestSelling' => $bestSelling,
            'lowStock' => $lowStock,
            'activeRentalList' => $activeRentalList,
            'unitStats' => $unitStats,
            'chartLabels' => $dailyLabels,
            'chartPos' => $dailyPos,
            'chartRental' => $dailyRental,
            'chartCashIn' => $cashIn,
            'chartCashOut' => $cashOut,
        ]);
    }
}
