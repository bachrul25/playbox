<?php

namespace App\Livewire;

use App\Livewire\Concerns\HasRoleGuard;
use App\Models\Expense;
use App\Models\User;
use App\Repositories\ExpenseRepository;
use App\Repositories\PartnerRepository;
use App\Repositories\PlayboxRepository;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseComponent extends Component
{
    use HasRoleGuard, WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'type')]
    public string $typeFilter = '';

    #[Url(as: 'from')]
    public string $from = '';

    #[Url(as: 'to')]
    public string $to = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public ?int $playbox_id = null;

    public ?int $partner_id = null;

    public string $expense_date = '';

    public string $type = 'maintenance';

    public string $amount = '0';

    public string $description = '';

    public function mount(): void
    {
        $this->authorizeRoles([User::ROLE_ADMIN, User::ROLE_OWNER]);
        $this->expense_date = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingFrom(): void
    {
        $this->resetPage();
    }

    public function updatingTo(): void
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
        $e = Expense::findOrFail($id);
        $this->editingId = $e->id;
        $this->playbox_id = $e->playbox_id;
        $this->partner_id = $e->partner_id;
        $this->expense_date = optional($e->expense_date)->toDateString() ?? now()->toDateString();
        $this->type = $e->type;
        $this->amount = (string) $e->amount;
        $this->description = (string) $e->description;
        $this->showModal = true;
    }

    public function save(ExpenseRepository $repo): void
    {
        $this->authorizeRoles([User::ROLE_ADMIN]);
        $data = $this->validate([
            'playbox_id' => ['nullable', 'integer', 'exists:playboxes,id'],
            'partner_id' => ['nullable', 'integer', 'exists:partners,id'],
            'expense_date' => ['required', 'date'],
            'type' => ['required', 'in:maintenance,perawatan,kerusakan,staff,lainnya'],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        if ($this->editingId) {
            $repo->update(Expense::findOrFail($this->editingId), $data);
            $this->dispatch('toast', type: 'success', message: 'Biaya berhasil diperbarui.');
        } else {
            $repo->create($data);
            $this->dispatch('toast', type: 'success', message: 'Biaya berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id, ExpenseRepository $repo): void
    {
        $this->authorizeRoles([User::ROLE_ADMIN]);
        $repo->delete(Expense::findOrFail($id));
        $this->dispatch('toast', type: 'success', message: 'Biaya dihapus.');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->playbox_id = null;
        $this->partner_id = null;
        $this->expense_date = now()->toDateString();
        $this->type = 'maintenance';
        $this->amount = '0';
        $this->description = '';
        $this->resetErrorBag();
    }

    #[Layout('layouts.app')]
    #[Title('Biaya')]
    public function render(ExpenseRepository $repo, PlayboxRepository $playboxes, PartnerRepository $partners)
    {
        $from = $this->from ?: null;
        $to = $this->to ?: null;

        return view('livewire.expense-component', [
            'expenses' => $repo->paginate($this->search, $this->typeFilter, $from, $to),
            'playboxList' => $playboxes->listAvailable(),
            'partnerList' => $partners->active(),
            'totalAmount' => $repo->totalByPeriod($from, $to),
        ]);
    }
}
