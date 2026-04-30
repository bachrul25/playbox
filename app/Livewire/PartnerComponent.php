<?php

namespace App\Livewire;

use App\Livewire\Concerns\HasRoleGuard;
use App\Models\Partner;
use App\Models\User;
use App\Repositories\PartnerRepository;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PartnerComponent extends Component
{
    use HasRoleGuard, WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $cafe_name = '';

    public string $person_in_charge = '';

    public string $phone = '';

    public string $address = '';

    public string $cooperation_start_date = '';

    public string $status = 'aktif';

    public string $note = '';

    public function mount(): void
    {
        $this->authorizeRoles([User::ROLE_ADMIN, User::ROLE_OWNER]);
        $this->cooperation_start_date = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $p = Partner::findOrFail($id);
        $this->editingId = $p->id;
        $this->cafe_name = $p->cafe_name;
        $this->person_in_charge = $p->person_in_charge;
        $this->phone = (string) $p->phone;
        $this->address = (string) $p->address;
        $this->cooperation_start_date = optional($p->cooperation_start_date)->toDateString() ?? now()->toDateString();
        $this->status = $p->status;
        $this->note = (string) $p->note;
        $this->showModal = true;
    }

    public function save(PartnerRepository $repo): void
    {
        $this->authorizeRoles([User::ROLE_ADMIN]);
        $data = $this->validate([
            'cafe_name' => ['required', 'string', 'max:120'],
            'person_in_charge' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'cooperation_start_date' => ['nullable', 'date'],
            'status' => ['required', 'in:aktif,tidak_aktif'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($this->editingId) {
            $repo->update(Partner::findOrFail($this->editingId), $data);
            $this->dispatch('toast', type: 'success', message: 'Mitra berhasil diperbarui.');
        } else {
            $repo->create($data);
            $this->dispatch('toast', type: 'success', message: 'Mitra berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id, PartnerRepository $repo): void
    {
        $this->authorizeRoles([User::ROLE_ADMIN]);
        $repo->delete(Partner::findOrFail($id));
        $this->dispatch('toast', type: 'success', message: 'Mitra berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->cafe_name = '';
        $this->person_in_charge = '';
        $this->phone = '';
        $this->address = '';
        $this->cooperation_start_date = now()->toDateString();
        $this->status = 'aktif';
        $this->note = '';
        $this->resetErrorBag();
    }

    #[Layout('layouts.app')]
    #[Title('Data Mitra/Cafe')]
    public function render(PartnerRepository $repo)
    {
        return view('livewire.partner-component', [
            'partners' => $repo->paginate($this->search, $this->statusFilter),
        ]);
    }
}
