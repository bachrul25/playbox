<?php

namespace App\Repositories;

use App\Models\PartnershipReport;
use App\Models\Playbox;
use App\Models\PrivateReport;
use App\Models\Rental;
use App\Support\RentalCalculator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RentalRepository
{
    public function paginate(string $search = '', string $type = '', string $paymentStatus = '', int $perPage = 10): LengthAwarePaginator
    {
        return Rental::query()
            ->with(['playbox', 'partner', 'user'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('invoice_number', 'like', "%$search%")
                        ->orWhere('customer_name', 'like', "%$search%")
                        ->orWhereHas('playbox', fn ($p) => $p->where('name', 'like', "%$search%")
                            ->orWhere('code', 'like', "%$search%"));
                });
            })
            ->when($type !== '', fn ($q) => $q->where('rental_type', $type))
            ->when($paymentStatus !== '', fn ($q) => $q->where('payment_status', $paymentStatus))
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * Buat rental + private/partnership report secara atomik.
     */
    public function createWithReport(array $data): Rental
    {
        return DB::transaction(function () use ($data) {
            $playbox = Playbox::findOrFail($data['playbox_id']);

            $duration = RentalCalculator::durationInHours(
                $data['start_time'],
                $data['end_time'],
                $data['rental_date'] ?? null,
            );
            $totalIncome = round($duration * (float) $data['price_per_hour'], 2);

            $rental = Rental::create([
                'invoice_number' => RentalCalculator::generateInvoiceNumber(),
                'playbox_id' => $playbox->id,
                'partner_id' => $data['rental_type'] === Rental::TYPE_KERJASAMA ? ($data['partner_id'] ?? $playbox->partner_id) : null,
                'user_id' => $data['user_id'],
                'rental_type' => $data['rental_type'],
                'rental_date' => $data['rental_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'duration' => $duration,
                'price_per_hour' => $data['price_per_hour'],
                'total_income' => $totalIncome,
                'payment_method' => $data['payment_method'],
                'payment_status' => $data['payment_status'],
                'customer_name' => $data['customer_name'] ?? null,
                'note' => $data['note'] ?? null,
            ]);

            if ($rental->rental_type === Rental::TYPE_PRIBADI) {
                $calc = RentalCalculator::calculatePrivate($totalIncome);
                PrivateReport::create([
                    'rental_id' => $rental->id,
                    'total_income' => $totalIncome,
                    'maintenance_amount' => $calc['maintenance'],
                    'owner_profit' => $calc['owner_profit'],
                    'maintenance_percentage' => RentalCalculator::MAINTENANCE_PERCENTAGE,
                    'owner_percentage' => RentalCalculator::OWNER_PERCENTAGE,
                    'report_date' => $rental->rental_date,
                ]);
            } else {
                $calc = RentalCalculator::calculatePartnership($totalIncome);
                PartnershipReport::create([
                    'rental_id' => $rental->id,
                    'partner_id' => $rental->partner_id,
                    'total_income' => $totalIncome,
                    'staff_cost' => $calc['staff_cost'],
                    'net_income' => $calc['net_income'],
                    'owner_share' => $calc['owner_share'],
                    'partner_share' => $calc['partner_share'],
                    'share_percentage' => RentalCalculator::SHARE_PERCENTAGE,
                    'report_date' => $rental->rental_date,
                ]);
            }

            return $rental->load(['playbox', 'partner', 'privateReport', 'partnershipReport']);
        });
    }

    public function delete(Rental $rental): bool
    {
        return (bool) $rental->delete();
    }
}
