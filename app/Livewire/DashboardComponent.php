<?php

namespace App\Livewire;

use App\Repositories\DashboardRepository;
use App\Repositories\ExpenseRepository;
use Carbon\CarbonImmutable;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class DashboardComponent extends Component
{
    #[Layout('layouts.app')]
    #[Title('Dashboard - PlayBox Rental')]
    public function render(DashboardRepository $repo, ExpenseRepository $expenses)
    {
        $year = (int) CarbonImmutable::now()->year;

        return view('livewire.dashboard-component', [
            'todayIncome' => $repo->todayIncome(),
            'monthIncome' => $repo->monthIncome(),
            'totalRentals' => $repo->totalRentals(),
            'totalPlayboxes' => $repo->totalActivePlayboxes(),
            'totalPartners' => $repo->totalActivePartners(),
            'privateIncome' => $repo->totalPrivateIncome(),
            'partnershipIncome' => $repo->totalPartnershipIncome(),
            'totalMaintenance' => $repo->totalMaintenance(),
            'ownerProfit' => $repo->totalOwnerProfit(),
            'monthlyChart' => $repo->monthlyIncomeChart($year),
            'comparisonChart' => $repo->privateVsPartnership(),
            'recentRentals' => $repo->recentRentals(8),
            'totalExpenses' => $expenses->totalByPeriod(),
            'currentYear' => $year,
        ]);
    }
}
