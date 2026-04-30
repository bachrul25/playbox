<?php

namespace App\Livewire;

use App\Models\FinanceCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Kategori Keuangan')]
#[Layout('layouts.app')]
class FinanceCategoryComponent extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $filterType = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $type = 'income';

    public string $description = '';

    public string $status = 'active';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'description']);
        $this->type = 'income';
        $this->status = 'active';
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $c = FinanceCategory::findOrFail($id);
        $this->editingId = $c->id;
        $this->name = $c->name;
        $this->type = $c->type;
        $this->description = (string) $c->description;
        $this->status = $c->status;
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
            FinanceCategory::find($this->editingId)?->update($data);
            $this->dispatch('toast', type: 'success', message: 'Kategori diperbarui.');
        } else {
            FinanceCategory::create($data);
            $this->dispatch('toast', type: 'success', message: 'Kategori ditambahkan.');
        }
        $this->showModal = false;
    }

    public function deleteCategory($id): void
    {
        $id = is_array($id) ? ($id[0] ?? null) : $id;
        if ($id) {
            FinanceCategory::find($id)?->delete();
            $this->dispatch('toast', type: 'success', message: 'Kategori dihapus.');
        }
    }

    public function render()
    {
        return view('livewire.finance-category-component', [
            'categories' => FinanceCategory::query()
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
                ->orderBy('type')->orderBy('name')
                ->paginate(10),
        ]);
    }
}
