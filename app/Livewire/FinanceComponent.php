<?php

namespace App\Livewire;

use App\Models\Cashflow;
use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Ringkasan Keuangan')]
#[Layout('layouts.app')]
class FinanceComponent extends Component
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

    public function render()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $totalIncome = Income::whereBetween('date', [$start, $end])->sum('amount');
        $totalExpense = Expense::whereBetween('date', [$start, $end])->sum('amount');

        // breakdowns
        $incomeBySource = Income::selectRaw('source, SUM(amount) total')
            ->whereBetween('date', [$start, $end])->groupBy('source')->pluck('total', 'source');
        $expenseByCategory = Expense::with('category')
            ->whereBetween('date', [$start, $end])->get()
            ->groupBy(fn ($e) => $e->category->name ?? 'Lainnya')
            ->map(fn ($g) => $g->sum('amount'));

        // daily cashflow within period
        $days = collect();
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $days->push($d->copy());
        }
        $labels = $days->map(fn ($d) => $d->format('d/m'));
        $inSeries = $days->map(fn ($d) => (float) Cashflow::where('type', 'in')->whereDate('date', $d)->sum('amount'));
        $outSeries = $days->map(fn ($d) => (float) Cashflow::where('type', 'out')->whereDate('date', $d)->sum('amount'));

        return view('livewire.finance-component', [
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'profit' => $totalIncome - $totalExpense,
            'incomeBySource' => $incomeBySource,
            'expenseByCategory' => $expenseByCategory,
            'labels' => $labels->values(),
            'inSeries' => $inSeries->values(),
            'outSeries' => $outSeries->values(),
        ]);
    }
}
