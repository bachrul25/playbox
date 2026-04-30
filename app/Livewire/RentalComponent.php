<?php

namespace App\Livewire;

use App\Models\Cashflow;
use App\Models\FinanceCategory;
use App\Models\Income;
use App\Models\PaymentMethod;
use App\Models\Rental;
use App\Models\RentalPayment;
use App\Models\RentalSession;
use App\Models\RentalUnit;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Rental Playbox')]
#[Layout('layouts.app')]
class RentalComponent extends Component
{
    public bool $showStartModal = false;

    public bool $showFinishModal = false;

    public bool $showExtendModal = false;

    public bool $showReceipt = false;

    public ?int $selectedUnitId = null;

    public ?int $activeRentalId = null;

    public string $customer_name = '';

    public string $mode = 'open'; // open | fixed

    public ?int $planned_minutes = null;

    public string $payment_method = 'Cash';

    public int $extendMinutes = 30;

    public ?array $lastReceipt = null;

    public function mount(): void
    {
        // refresh-friendly
    }

    public function openStart(int $unitId): void
    {
        $unit = RentalUnit::find($unitId);
        if (! $unit) {
            return;
        }
        if (! in_array($unit->status, ['available'], true)) {
            $this->dispatch('toast', type: 'error', message: 'Unit tidak tersedia.');

            return;
        }
        $this->selectedUnitId = $unitId;
        $this->customer_name = '';
        $this->mode = 'open';
        $this->planned_minutes = null;
        $this->showStartModal = true;
    }

    public function startRental(): void
    {
        $this->validate([
            'selectedUnitId' => 'required|exists:rental_units,id',
            'customer_name' => 'required|string|max:120',
            'mode' => 'required|in:open,fixed',
            'planned_minutes' => 'nullable|integer|min:1',
            'payment_method' => 'required|string',
        ]);

        $unit = RentalUnit::lockForUpdate()->find($this->selectedUnitId);
        if (! $unit || $unit->status !== 'available') {
            $this->dispatch('toast', type: 'error', message: 'Unit tidak tersedia.');

            return;
        }

        DB::transaction(function () use ($unit) {
            $invoice = 'RNT-'.now()->format('Ymd').'-'.str_pad((string) (Rental::whereDate('created_at', today())->count() + 1), 4, '0', STR_PAD_LEFT);
            $rental = Rental::create([
                'user_id' => auth()->id(),
                'rental_unit_id' => $unit->id,
                'invoice_number' => $invoice,
                'customer_name' => $this->customer_name,
                'start_time' => now(),
                'duration_minutes' => 0,
                'hourly_price' => $unit->hourly_price,
                'total_price' => 0,
                'payment_method' => $this->payment_method,
                'status' => 'active',
                'mode' => $this->mode,
                'planned_minutes' => $this->mode === 'fixed' ? $this->planned_minutes : null,
            ]);
            RentalSession::create([
                'rental_id' => $rental->id,
                'rental_unit_id' => $unit->id,
                'start_time' => now(),
                'status' => 'active',
            ]);
            $unit->update(['status' => 'in_use']);
        });

        $this->showStartModal = false;
        $this->dispatch('toast', type: 'success', message: 'Sesi rental dimulai.');
    }

    public function openExtend(int $rentalId): void
    {
        $this->activeRentalId = $rentalId;
        $this->extendMinutes = 30;
        $this->showExtendModal = true;
    }

    public function extend(): void
    {
        $this->validate(['extendMinutes' => 'required|integer|min:1']);

        $rental = Rental::find($this->activeRentalId);
        if (! $rental || $rental->status !== 'active') {
            return;
        }

        $rental->planned_minutes = ($rental->planned_minutes ?? 0) + $this->extendMinutes;
        $rental->save();

        RentalSession::create([
            'rental_id' => $rental->id,
            'rental_unit_id' => $rental->rental_unit_id,
            'start_time' => now(),
            'additional_minutes' => $this->extendMinutes,
            'status' => 'active',
        ]);

        $this->showExtendModal = false;
        $this->dispatch('toast', type: 'success', message: 'Waktu rental diperpanjang '.$this->extendMinutes.' menit.');
    }

    public function openFinish(int $rentalId): void
    {
        $this->activeRentalId = $rentalId;
        $this->payment_method = 'Cash';
        $this->showFinishModal = true;
    }

    public function getCalculatedProperty(): array
    {
        $rental = Rental::with('unit')->find($this->activeRentalId);
        if (! $rental) {
            return ['minutes' => 0, 'total' => 0];
        }
        $minutes = max(1, $rental->start_time->diffInMinutes(now()));
        $total = round(($minutes / 60) * (float) $rental->hourly_price);

        return ['minutes' => $minutes, 'total' => $total, 'rental' => $rental];
    }

    public function finish(): void
    {
        $rental = Rental::with('unit')->find($this->activeRentalId);
        if (! $rental || $rental->status !== 'active') {
            return;
        }

        $minutes = max(1, $rental->start_time->diffInMinutes(now()));
        $total = round(($minutes / 60) * (float) $rental->hourly_price);

        DB::transaction(function () use ($rental, $minutes, $total) {
            $rental->update([
                'end_time' => now(),
                'duration_minutes' => $minutes,
                'total_price' => $total,
                'payment_method' => $this->payment_method,
                'status' => 'finished',
            ]);
            $rental->sessions()->where('status', 'active')->update([
                'end_time' => now(),
                'status' => 'finished',
            ]);
            RentalUnit::find($rental->rental_unit_id)?->update(['status' => 'available']);
            RentalPayment::create([
                'rental_id' => $rental->id,
                'amount' => $total,
                'payment_method' => $this->payment_method,
                'payment_date' => now(),
            ]);
            $cat = FinanceCategory::firstWhere('name', 'Rental Playbox');
            Income::create([
                'source' => 'rental',
                'reference_id' => $rental->id,
                'category_id' => $cat?->id,
                'amount' => $total,
                'description' => 'Rental '.$rental->invoice_number.' - '.($rental->unit->name ?? '-'),
                'date' => today(),
            ]);
            Cashflow::create([
                'type' => 'in',
                'source' => 'rental',
                'reference_id' => $rental->id,
                'amount' => $total,
                'description' => 'Rental '.$rental->invoice_number,
                'date' => today(),
            ]);
        });

        $rental->refresh();
        $this->lastReceipt = [
            'invoice' => $rental->invoice_number,
            'customer' => $rental->customer_name,
            'unit' => $rental->unit->name ?? '-',
            'start' => $rental->start_time->format('d/m/Y H:i'),
            'end' => $rental->end_time->format('d/m/Y H:i'),
            'minutes' => $minutes,
            'hourly' => (float) $rental->hourly_price,
            'total' => $total,
            'method' => $rental->payment_method,
        ];

        $this->showFinishModal = false;
        $this->showReceipt = true;
        $this->dispatch('toast', type: 'success', message: 'Sesi rental selesai.');
    }

    public function closeReceipt(): void
    {
        $this->showReceipt = false;
        $this->lastReceipt = null;
    }

    public function render()
    {
        $units = RentalUnit::with(['activeRental'])->orderBy('code')->get();
        $activeRentals = Rental::with('unit')->where('status', 'active')->get();

        return view('livewire.rental-component', [
            'units' => $units,
            'activeRentals' => $activeRentals,
            'paymentMethods' => PaymentMethod::where('status', 'active')->get(),
            'startUnit' => $this->selectedUnitId ? RentalUnit::find($this->selectedUnitId) : null,
            'activeRental' => $this->activeRentalId ? Rental::with('unit')->find($this->activeRentalId) : null,
        ]);
    }
}
