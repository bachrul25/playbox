<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Kategori Produk')]
#[Layout('layouts.app')]
class CategoryComponent extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public string $status = 'active';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'description']);
        $this->status = 'active';
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $cat = Category::findOrFail($id);
        $this->editingId = $cat->id;
        $this->name = $cat->name;
        $this->description = (string) $cat->description;
        $this->status = $cat->status;
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
            Category::find($this->editingId)?->update($data);
            $this->dispatch('toast', type: 'success', message: 'Kategori diperbarui.');
        } else {
            Category::create($data);
            $this->dispatch('toast', type: 'success', message: 'Kategori ditambahkan.');
        }
        $this->showModal = false;
    }

    public function deleteCategory($id): void
    {
        $id = is_array($id) ? ($id[0] ?? null) : $id;
        if ($id) {
            Category::find($id)?->delete();
            $this->dispatch('toast', type: 'success', message: 'Kategori dihapus.');
        }
    }

    public function render()
    {
        return view('livewire.category-component', [
            'categories' => Category::query()
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(10),
        ]);
    }
}
