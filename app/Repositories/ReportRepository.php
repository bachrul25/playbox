<?php

namespace App\Repositories;

use App\Models\PartnershipReport;
use App\Models\PrivateReport;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class ReportRepository
{
    /**
     * Build a date range from filter type or explicit dates.
     *
     * @return array{0: ?string, 1: ?string}
     */
    public function buildRange(?string $period = null, ?string $from = null, ?string $to = null): array
    {
        if ($from && $to) {
            return [$from, $to];
        }
        $now = CarbonImmutable::now();

        return match ($period) {
            'harian' => [$now->startOfDay()->toDateString(), $now->endOfDay()->toDateString()],
            'mingguan' => [$now->startOfWeek()->toDateString(), $now->endOfWeek()->toDateString()],
            'bulanan' => [$now->startOfMonth()->toDateString(), $now->endOfMonth()->toDateString()],
            'tahunan' => [$now->startOfYear()->toDateString(), $now->endOfYear()->toDateString()],
            default => [null, null],
        };
    }

    public function privateReports(?string $from, ?string $to): Builder
    {
        return PrivateReport::query()
            ->with(['rental.playbox'])
            ->when($from, fn ($q) => $q->whereDate('report_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('report_date', '<=', $to))
            ->orderByDesc('report_date');
    }

    public function partnershipReports(?string $from, ?string $to, ?int $partnerId = null): Builder
    {
        return PartnershipReport::query()
            ->with(['rental.playbox', 'partner'])
            ->when($from, fn ($q) => $q->whereDate('report_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('report_date', '<=', $to))
            ->when($partnerId, fn ($q) => $q->where('partner_id', $partnerId))
            ->orderByDesc('report_date');
    }

    /**
     * @return array{total_income: float, maintenance: float, owner_profit: float, count: int}
     */
    public function privateSummary(?string $from, ?string $to): array
    {
        $row = PrivateReport::query()
            ->when($from, fn ($q) => $q->whereDate('report_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('report_date', '<=', $to))
            ->selectRaw('COALESCE(SUM(total_income),0) as total_income, COALESCE(SUM(maintenance_amount),0) as maintenance, COALESCE(SUM(owner_profit),0) as owner_profit, COUNT(*) as cnt')
            ->first();

        return [
            'total_income' => (float) ($row->total_income ?? 0),
            'maintenance' => (float) ($row->maintenance ?? 0),
            'owner_profit' => (float) ($row->owner_profit ?? 0),
            'count' => (int) ($row->cnt ?? 0),
        ];
    }

    /**
     * @return array{total_income: float, staff_cost: float, net_income: float, owner_share: float, partner_share: float, count: int}
     */
    public function partnershipSummary(?string $from, ?string $to, ?int $partnerId = null): array
    {
        $row = PartnershipReport::query()
            ->when($from, fn ($q) => $q->whereDate('report_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('report_date', '<=', $to))
            ->when($partnerId, fn ($q) => $q->where('partner_id', $partnerId))
            ->selectRaw('COALESCE(SUM(total_income),0) as total_income, COALESCE(SUM(staff_cost),0) as staff_cost, COALESCE(SUM(net_income),0) as net_income, COALESCE(SUM(owner_share),0) as owner_share, COALESCE(SUM(partner_share),0) as partner_share, COUNT(*) as cnt')
            ->first();

        return [
            'total_income' => (float) ($row->total_income ?? 0),
            'staff_cost' => (float) ($row->staff_cost ?? 0),
            'net_income' => (float) ($row->net_income ?? 0),
            'owner_share' => (float) ($row->owner_share ?? 0),
            'partner_share' => (float) ($row->partner_share ?? 0),
            'count' => (int) ($row->cnt ?? 0),
        ];
    }
}
