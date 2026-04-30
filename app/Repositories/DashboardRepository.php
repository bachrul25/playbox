<?php

namespace App\Repositories;

use App\Models\Expense;
use App\Models\Partner;
use App\Models\PartnershipReport;
use App\Models\Playbox;
use App\Models\PrivateReport;
use App\Models\Rental;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

class DashboardRepository
{
    public function todayIncome(): float
    {
        return (float) Rental::query()->whereDate('rental_date', CarbonImmutable::today())->sum('total_income');
    }

    public function monthIncome(): float
    {
        $now = CarbonImmutable::now();

        return (float) Rental::query()
            ->whereBetween('rental_date', [$now->startOfMonth()->toDateString(), $now->endOfMonth()->toDateString()])
            ->sum('total_income');
    }

    public function totalRentals(): int
    {
        return Rental::query()->count();
    }

    public function totalActivePlayboxes(): int
    {
        return Playbox::query()->where('status', '!=', Playbox::STATUS_TIDAK_AKTIF)->count();
    }

    public function totalActivePartners(): int
    {
        return Partner::query()->where('status', 'aktif')->count();
    }

    public function totalPrivateIncome(): float
    {
        return (float) PrivateReport::query()->sum('total_income');
    }

    public function totalPartnershipIncome(): float
    {
        return (float) PartnershipReport::query()->sum('total_income');
    }

    public function totalMaintenance(): float
    {
        $maintenanceFromReports = (float) PrivateReport::query()->sum('maintenance_amount');
        $maintenanceExpenses = (float) Expense::query()->whereIn('type', ['maintenance', 'perawatan', 'kerusakan'])->sum('amount');

        return $maintenanceFromReports + $maintenanceExpenses;
    }

    public function totalOwnerProfit(): float
    {
        $privateProfit = (float) PrivateReport::query()->sum('owner_profit');
        $partnershipProfit = (float) PartnershipReport::query()->sum('owner_share');

        return $privateProfit + $partnershipProfit;
    }

    /**
     * @return array{labels: array<int,string>, data: array<int,float>}
     */
    public function monthlyIncomeChart(int $year): array
    {
        $rows = Rental::query()
            ->selectRaw("strftime('%m', rental_date) as month, SUM(total_income) as total")
            ->whereYear('rental_date', $year)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Fallback for MySQL when not running on sqlite.
        if (empty($rows)) {
            $rows = Rental::query()
                ->selectRaw('MONTH(rental_date) as month, SUM(total_income) as total')
                ->whereYear('rental_date', $year)
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray();
        }

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = str_pad((string) $m, 2, '0', STR_PAD_LEFT);
            $data[] = (float) ($rows[$key] ?? $rows[$m] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * @return array{labels: array<int,string>, pribadi: float, kerjasama: float}
     */
    public function privateVsPartnership(): array
    {
        return [
            'labels' => ['Pribadi', 'Kerjasama'],
            'pribadi' => $this->totalPrivateIncome(),
            'kerjasama' => $this->totalPartnershipIncome(),
        ];
    }

    public function recentRentals(int $limit = 5): Collection
    {
        return Rental::query()->with(['playbox', 'partner'])->latest('id')->limit($limit)->get();
    }
}
