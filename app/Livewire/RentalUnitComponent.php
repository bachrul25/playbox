<?php

namespace App\Livewire;

use App\Models\RentalUnit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Unit Rental Playbox')]
#[Layout('layouts.app')]
class RentalUnitComponent extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $filterStatus = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $code = '';

    public string $name = '';

    public string $type = 'PS4';

    public float $hourly_price = 0;

    public string $status = 'available';

    public string $location = '';

    public string $description = '';

    protected function rules(): array
    {
        return [
            'code' => 'required|string|max:30',
            'name' => 'required|string|max:120',
            'type' => 'required|string|max:50',
            'hourly_price' => 'required|numeric|min:0',
            'status' => 'required|in:available,in_use,maintenance,inactive',
            'location' => 'nullable|string|max:120',
            'description' => 'nullable|string',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'code', 'name', 'hourly_price', 'location', 'description']);
        $this->type = 'PS4';
        $this->status = 'available';
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $u = RentalUnit::findOrFail($id);
        $this->editingId = $u->id;
        $this->code = $u->code;
        $this->name = $u->name;
        $this->type = $u->type;
        $this->hourly_price = (float) $u->hourly_price;
        $this->status = $u->status;
        $this->location = (string) $u->location;
        $this->description = (string) $u->description;
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->editingId) {
            RentalUnit::find($this->editingId)?->update($data);
            $this->dispatch('toast', type: 'success', message: 'Unit rental diperbarui.');
        } else {
            RentalUnit::create($data);
            $this->dispatch('toast', type: 'success', message: 'Unit rental ditambahkan.');
        }
        $this->showModal = false;
    }

    public function deleteUnit($id): void
    {
        $id = is_array($id) ? ($id[0] ?? null) : $id;
        if ($id) {
            $unit = RentalUnit::find($id);
            if ($unit && $unit->status === 'in_use') {
                $this->dispatch('toast', type: 'error', message: 'Unit sedang aktif disewa.');

                return;
            }
            $unit?->delete();
            $this->dispatch('toast', type: 'success', message: 'Unit dihapus.');
        }
    }

    public function render()
    {
        return view('livewire.rental-unit-component', [
            'units' => RentalUnit::query()
                ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2->where('code', 'like', "%{$this->search}%")->orWhere('name', 'like', "%{$this->search}%")))
                ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
                ->orderBy('code')
                ->paginate(10),
        ]);
    }
}
