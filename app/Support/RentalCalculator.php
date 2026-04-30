<?php

namespace App\Support;

/**
 * Aturan perhitungan rental PlayBox.
 *
 * Pribadi:
 *   maintenance = total_income * 20%
 *   owner_profit = total_income * 80%
 *
 * Kerjasama:
 *   staff_cost   = Rp800.000 (tetap)
 *   net_income   = total_income - staff_cost
 *   owner_share  = net_income * 50%
 *   partner_share= net_income * 50%
 */
class RentalCalculator
{
    public const STAFF_COST = 800000;

    public const MAINTENANCE_PERCENTAGE = 20;

    public const OWNER_PERCENTAGE = 80;

    public const SHARE_PERCENTAGE = 50;

    /**
     * @return array{maintenance: float, owner_profit: float}
     */
    public static function calculatePrivate(float $totalIncome): array
    {
        $maintenance = round($totalIncome * self::MAINTENANCE_PERCENTAGE / 100, 2);
        $ownerProfit = round($totalIncome * self::OWNER_PERCENTAGE / 100, 2);

        return [
            'maintenance' => $maintenance,
            'owner_profit' => $ownerProfit,
        ];
    }

    /**
     * @return array{staff_cost: float, net_income: float, owner_share: float, partner_share: float, sufficient: bool}
     */
    public static function calculatePartnership(float $totalIncome, float $staffCost = self::STAFF_COST): array
    {
        $sufficient = $totalIncome >= $staffCost;
        $netIncome = max(0, $totalIncome - $staffCost);
        $ownerShare = round($netIncome * self::SHARE_PERCENTAGE / 100, 2);
        $partnerShare = round($netIncome * self::SHARE_PERCENTAGE / 100, 2);

        return [
            'staff_cost' => $staffCost,
            'net_income' => $netIncome,
            'owner_share' => $ownerShare,
            'partner_share' => $partnerShare,
            'sufficient' => $sufficient,
        ];
    }

    /**
     * Hitung durasi (jam) dari jam mulai dan jam selesai.
     * Mendukung melewati tengah malam.
     */
    public static function durationInHours(string $startTime, string $endTime, ?string $rentalDate = null): float
    {
        $date = $rentalDate ?? '1970-01-01';
        $start = strtotime("$date $startTime");
        $end = strtotime("$date $endTime");

        if ($end <= $start) {
            $end = strtotime("$date $endTime +1 day");
        }

        $seconds = max(0, $end - $start);

        return round($seconds / 3600, 2);
    }

    public static function generateInvoiceNumber(): string
    {
        return 'INV-'.date('Ymd').'-'.strtoupper(substr(md5(uniqid('', true)), 0, 6));
    }
}
