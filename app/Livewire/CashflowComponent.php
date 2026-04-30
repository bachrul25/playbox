<?php

namespace App\Livewire;

use App\Models\Cashflow;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Arus Kas')]
#[Layout('layouts.app')]
class CashflowComponent extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $filterType = '';

    #[Url(history: true)]
    public string $filterSource = '';

    #[Url(history: true)]
    public string $startDate = '';

    #[Url(history: true)]
    public string $endDate = '';

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatingFilterSource()
    {
        $this->resetPage();
    }

    public function render()
    {
        $q = Cashflow::query()
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->when($this->filterSource, fn ($q) => $q->where('source', $this->filterSource))
            ->when($this->startDate, fn ($q) => $q->whereDate('date', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('date', '<=', $this->endDate))
            ->orderByDesc('date')->orderByDesc('id');

        $totalIn = (clone $q)->where('type', 'in')->sum('amount');
        $totalOut = (clone $q)->where('type', 'out')->sum('amount');

        return view('livewire.cashflow-component', [
            'flows' => $q->paginate(15),
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'net' => $totalIn - $totalOut,
        ]);
    }
}
