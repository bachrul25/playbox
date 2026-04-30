<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Produk')]
#[Layout('layouts.app')]
class ProductComponent extends Component
{
    use WithFileUploads, WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public ?int $filterCategory = null;

    #[Url(history: true)]
    public string $filterStatus = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public ?int $category_id = null;

    public float $purchase_price = 0;

    public float $selling_price = 0;

    public int $stock = 0;

    public int $minimum_stock = 0;

    public string $status = 'active';

    public $imageFile = null;

    public ?string $existingImage = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'category_id' => 'nullable|exists:categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
            'imageFile' => 'nullable|image|max:2048',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterCategory()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'category_id', 'purchase_price', 'selling_price', 'stock', 'minimum_stock', 'imageFile', 'existingImage']);
        $this->status = 'active';
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $p = Product::findOrFail($id);
        $this->editingId = $p->id;
        $this->name = $p->name;
        $this->category_id = $p->category_id;
        $this->purchase_price = (float) $p->purchase_price;
        $this->selling_price = (float) $p->selling_price;
        $this->stock = (int) $p->stock;
        $this->minimum_stock = (int) $p->minimum_stock;
        $this->status = $p->status;
        $this->existingImage = $p->image;
        $this->imageFile = null;
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
        unset($data['imageFile']);

        if ($this->imageFile) {
            $data['image'] = $this->imageFile->store('products', 'public');
        } elseif ($this->existingImage) {
            $data['image'] = $this->existingImage;
        }

        if ($this->editingId) {
            $existing = Product::find($this->editingId);
            $oldStock = $existing->stock;
            $existing->update($data);
            if ($existing->stock !== $oldStock) {
                StockLog::create([
                    'product_id' => $existing->id,
                    'type' => 'adjust',
                    'quantity' => $existing->stock - $oldStock,
                    'description' => 'Penyesuaian stok manual',
                    'created_by' => auth()->id(),
                ]);
            }
            $this->dispatch('toast', type: 'success', message: 'Produk diperbarui.');
        } else {
            $p = Product::create($data);
            if ($p->stock > 0) {
                StockLog::create([
                    'product_id' => $p->id,
                    'type' => 'in',
                    'quantity' => $p->stock,
                    'description' => 'Stok awal produk',
                    'created_by' => auth()->id(),
                ]);
            }
            $this->dispatch('toast', type: 'success', message: 'Produk ditambahkan.');
        }
        $this->showModal = false;
    }

    public function deleteProduct($id): void
    {
        $id = is_array($id) ? ($id[0] ?? null) : $id;
        if ($id) {
            Product::find($id)?->delete();
            $this->dispatch('toast', type: 'success', message: 'Produk dihapus.');
        }
    }

    public function render()
    {
        return view('livewire.product-component', [
            'products' => Product::with('category')
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
                ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
                ->orderBy('name')
                ->paginate(10),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
