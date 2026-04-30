<?php

namespace App\Livewire;

use App\Models\PaymentMethod;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Metode Pembayaran')]
#[Layout('layouts.app')]
class PaymentMethodComponent extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $status = 'active';

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name']);
        $this->status = 'active';
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $pm = PaymentMethod::findOrFail($id);
        $this->editingId = $pm->id;
        $this->name = $pm->name;
        $this->status = $pm->status;
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:80',
            'status' => 'required|in:active,inactive',
        ]);
        if ($this->editingId) {
            PaymentMethod::find($this->editingId)?->update($data);
            $this->dispatch('toast', type: 'success', message: 'Metode pembayaran diperbarui.');
        } else {
            PaymentMethod::create($data);
            $this->dispatch('toast', type: 'success', message: 'Metode pembayaran ditambahkan.');
        }
        $this->showModal = false;
    }

    public function deleteMethod($id): void
    {
        $id = is_array($id) ? ($id[0] ?? null) : $id;
        if ($id) {
            PaymentMethod::find($id)?->delete();
            $this->dispatch('toast', type: 'success', message: 'Metode dihapus.');
        }
    }

    public function render()
    {
        return view('livewire.payment-method-component', [
            'methods' => PaymentMethod::orderBy('name')->paginate(10),
        ]);
    }
}
