<?php

namespace App\Livewire;

use App\Models\Cashflow;
use App\Models\Expense;
use App\Models\FinanceCategory;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Pengeluaran')]
#[Layout('layouts.app')]
class ExpenseComponent extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public ?int $filterCategory = null;

    #[Url(history: true)]
    public string $startDate = '';

    #[Url(history: true)]
    public string $endDate = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public ?int $category_id = null;

    public float $amount = 0;

    public string $description = '';

    public string $date = '';

    protected function rules(): array
    {
        return [
            'category_id' => 'nullable|exists:finance_categories,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'date' => 'required|date',
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

    public function openCreate(): void
    {
        $this->reset(['editingId', 'amount', 'description', 'category_id']);
        $this->date = now()->toDateString();
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $e = Expense::findOrFail($id);
        $this->editingId = $e->id;
        $this->category_id = $e->category_id;
        $this->amount = (float) $e->amount;
        $this->description = (string) $e->description;
        $this->date = $e->date->toDateString();
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
            $e = Expense::find($this->editingId);
            $e->update($data);
            Cashflow::where('source', 'expense')->where('reference_id', $e->id)->update([
                'amount' => $e->amount, 'description' => $e->description, 'date' => $e->date,
            ]);
            $this->dispatch('toast', type: 'success', message: 'Pengeluaran diperbarui.');
        } else {
            DB::transaction(function () use ($data) {
                $e = Expense::create($data);
                Cashflow::create([
                    'type' => 'out', 'source' => 'expense',
                    'reference_id' => $e->id,
                    'amount' => $e->amount, 'description' => $e->description, 'date' => $e->date,
                ]);
            });
            $this->dispatch('toast', type: 'success', message: 'Pengeluaran ditambahkan.');
        }
        $this->showModal = false;
    }

    public function deleteExpense($id): void
    {
        $id = is_array($id) ? ($id[0] ?? null) : $id;
        if (! $id) {
            return;
        }
        DB::transaction(function () use ($id) {
            Cashflow::where('source', 'expense')->where('reference_id', $id)->delete();
            Expense::find($id)?->delete();
        });
        $this->dispatch('toast', type: 'success', message: 'Pengeluaran dihapus.');
    }

    public function render()
    {
        $q = Expense::with('category')
            ->when($this->search, fn ($q) => $q->where('description', 'like', "%{$this->search}%"))
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->startDate, fn ($q) => $q->whereDate('date', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('date', '<=', $this->endDate))
            ->orderByDesc('date')->orderByDesc('id');

        return view('livewire.expense-component', [
            'expenses' => $q->paginate(10),
            'categories' => FinanceCategory::where('type', 'expense')->where('status', 'active')->get(),
        ]);
    }
}
