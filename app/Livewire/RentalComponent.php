<?php

namespace App\Livewire;

use App\Livewire\Concerns\HasRoleGuard;
use App\Models\Playbox;
use App\Models\Rental;
use App\Models\User;
use App\Repositories\PartnerRepository;
use App\Repositories\PlayboxRepository;
use App\Repositories\RentalRepository;
use App\Support\RentalCalculator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class RentalComponent extends Component
{
    use HasRoleGuard, WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'tipe')]
    public string $typeFilter = '';

    #[Url(as: 'bayar')]
    public string $paymentStatusFilter = '';

    public bool $showModal = false;

    public ?int $playbox_id = null;

    public ?int $partner_id = null;

    public string $rental_type = Rental::TYPE_PRIBADI;

    public string $rental_date = '';

    public string $start_time = '';

    public string $end_time = '';

    public string $price_per_hour = '0';

    public string $payment_method = 'cash';

    public string $payment_status = 'lunas';

    public string $customer_name = '';

    public string $note = '';

    public function mount(): void
    {
        $this->authorizeRoles([User::ROLE_ADMIN]);
        $this->rental_date = now()->toDateString();
        $this->start_time = '10:00';
        $this->end_time = '12:00';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPlayboxId($value): void
    {
        if ($value) {
            $playbox = Playbox::find($value);
            if ($playbox) {
                $this->price_per_hour = (string) $playbox->default_price_per_hour;
                if ($playbox->ownership_type === Playbox::OWNERSHIP_KERJASAMA) {
                    $this->rental_type = Rental::TYPE_KERJASAMA;
                    $this->partner_id = $playbox->partner_id;
                } else {
                    $this->rental_type = Rental::TYPE_PRIBADI;
                    $this->partner_id = null;
                }
            }
        }
    }

    public function getDurationProperty(): float
    {
        if (! $this->start_time || ! $this->end_time) {
            return 0;
        }

        return RentalCalculator::durationInHours($this->start_time, $this->end_time, $this->rental_date);
    }

    public function getTotalIncomeProperty(): float
    {
        return round($this->duration * (float) $this->price_per_hour, 2);
    }

    public function getCalculationPreviewProperty(): array
    {
        $total = $this->totalIncome;
        if ($this->rental_type === Rental::TYPE_PRIBADI) {
            return ['mode' => 'pribadi'] + RentalCalculator::calculatePrivate($total);
        }

        return ['mode' => 'kerjasama'] + RentalCalculator::calculatePartnership($total);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function save(RentalRepository $repo): void
    {
        $this->authorizeRoles([User::ROLE_ADMIN]);

        $data = $this->validate([
            'playbox_id' => ['required', 'integer', 'exists:playboxes,id'],
            'partner_id' => ['nullable', 'integer', 'exists:partners,id'],
            'rental_type' => ['required', 'in:pribadi,kerjasama'],
            'rental_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'different:start_time'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,transfer,qris'],
            'payment_status' => ['required', 'in:lunas,belum_lunas'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $playbox = Playbox::findOrFail($data['playbox_id']);
        if ($playbox->status === Playbox::STATUS_TIDAK_AKTIF) {
            $this->addError('playbox_id', 'PlayBox berstatus tidak aktif, tidak dapat disewa.');

            return;
        }

        if ($data['rental_type'] === Rental::TYPE_KERJASAMA && empty($data['partner_id'])) {
            $this->addError('partner_id', 'Mitra wajib dipilih untuk transaksi kerjasama.');

            return;
        }

        if (strtotime($data['end_time']) <= strtotime($data['start_time'])) {
            // izinkan jika lewat tengah malam — durasi kalkulator handle, tapi peringatkan jika sama
            if ($data['end_time'] === $data['start_time']) {
                $this->addError('end_time', 'Jam selesai tidak boleh sama dengan jam mulai.');

                return;
            }
        }

        $duration = RentalCalculator::durationInHours($data['start_time'], $data['end_time'], $data['rental_date']);
        $total = $duration * (float) $data['price_per_hour'];
        if ($data['rental_type'] === Rental::TYPE_KERJASAMA && $total < RentalCalculator::STAFF_COST) {
            $this->dispatch('toast', type: 'warning', message: 'Pendapatan kerjasama (Rp'.number_format($total, 0, ',', '.').') lebih kecil dari biaya staff Rp'.number_format(RentalCalculator::STAFF_COST, 0, ',', '.').'. Bagi hasil akan menjadi 0.');
        }

        $data['user_id'] = Auth::id();
        $repo->createWithReport($data);

        $this->dispatch('toast', type: 'success', message: 'Transaksi rental berhasil disimpan.');
        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id, RentalRepository $repo): void
    {
        $this->authorizeRoles([User::ROLE_ADMIN]);
        $repo->delete(Rental::findOrFail($id));
        $this->dispatch('toast', type: 'success', message: 'Transaksi dihapus.');
    }

    private function resetForm(): void
    {
        $this->playbox_id = null;
        $this->partner_id = null;
        $this->rental_type = Rental::TYPE_PRIBADI;
        $this->rental_date = now()->toDateString();
        $this->start_time = '10:00';
        $this->end_time = '12:00';
        $this->price_per_hour = '0';
        $this->payment_method = 'cash';
        $this->payment_status = 'lunas';
        $this->customer_name = '';
        $this->note = '';
        $this->resetErrorBag();
    }

    #[Layout('layouts.app')]
    #[Title('Transaksi Rental')]
    public function render(RentalRepository $repo, PlayboxRepository $playboxes, PartnerRepository $partners)
    {
        return view('livewire.rental-component', [
            'rentals' => $repo->paginate($this->search, $this->typeFilter, $this->paymentStatusFilter),
            'playboxes' => $playboxes->listAvailable(),
            'partners' => $partners->active(),
        ]);
    }
}
