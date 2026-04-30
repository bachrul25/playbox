<?php

namespace App\Livewire;

use App\Models\Cashflow;
use App\Models\FinanceCategory;
use App\Models\Income;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Pemasukan')]
#[Layout('layouts.app')]
class IncomeComponent extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $filterSource = '';

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

    public function updatingFilterSource()
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
        $i = Income::findOrFail($id);
        if ($i->source !== 'manual') {
            $this->dispatch('toast', type: 'error', message: 'Pemasukan otomatis tidak boleh diedit.');

            return;
        }
        $this->editingId = $i->id;
        $this->category_id = $i->category_id;
        $this->amount = (float) $i->amount;
        $this->description = (string) $i->description;
        $this->date = $i->date->toDateString();
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
            $income = Income::find($this->editingId);
            if ($income && $income->source === 'manual') {
                $income->update($data);
                Cashflow::where('source', 'manual_income')
                    ->where('reference_id', $income->id)
                    ->update([
                        'amount' => $data['amount'],
                        'description' => $data['description'],
                        'date' => $data['date'],
                    ]);
            }
            $this->dispatch('toast', type: 'success', message: 'Pemasukan diperbarui.');
        } else {
            DB::transaction(function () use ($data) {
                $income = Income::create(array_merge($data, ['source' => 'manual']));
                Cashflow::create([
                    'type' => 'in', 'source' => 'manual_income',
                    'reference_id' => $income->id,
                    'amount' => $income->amount, 'description' => $income->description,
                    'date' => $income->date,
                ]);
            });
            $this->dispatch('toast', type: 'success', message: 'Pemasukan ditambahkan.');
        }
        $this->showModal = false;
    }

    public function deleteIncome($id): void
    {
        $id = is_array($id) ? ($id[0] ?? null) : $id;
        if (! $id) {
            return;
        }
        $i = Income::find($id);
        if ($i && $i->source === 'manual') {
            DB::transaction(function () use ($i) {
                Cashflow::where('source', 'manual_income')->where('reference_id', $i->id)->delete();
                $i->delete();
            });
            $this->dispatch('toast', type: 'success', message: 'Pemasukan dihapus.');
        } else {
            $this->dispatch('toast', type: 'error', message: 'Pemasukan otomatis tidak boleh dihapus.');
        }
    }

    public function render()
    {
        $q = Income::with('category')
            ->when($this->search, fn ($q) => $q->where('description', 'like', "%{$this->search}%"))
            ->when($this->filterSource, fn ($q) => $q->where('source', $this->filterSource))
            ->when($this->startDate, fn ($q) => $q->whereDate('date', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('date', '<=', $this->endDate))
            ->orderByDesc('date')->orderByDesc('id');

        return view('livewire.income-component', [
            'incomes' => $q->paginate(10),
            'categories' => FinanceCategory::where('type', 'income')->where('status', 'active')->get(),
        ]);
    }
}
