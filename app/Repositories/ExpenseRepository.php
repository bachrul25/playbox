<?php

namespace App\Repositories;

use App\Models\Expense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ExpenseRepository
{
    public function paginate(string $search = '', string $type = '', ?string $from = null, ?string $to = null, int $perPage = 10): LengthAwarePaginator
    {
        return Expense::query()
            ->with(['playbox', 'partner'])
            ->when($search !== '', fn ($q) => $q->where('description', 'like', "%$search%"))
            ->when($type !== '', fn ($q) => $q->where('type', $type))
            ->when($from, fn ($q) => $q->whereDate('expense_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('expense_date', '<=', $to))
            ->latest('expense_date')
            ->paginate($perPage);
    }

    public function create(array $data): Expense
    {
        return Expense::create($data);
    }

    public function update(Expense $expense, array $data): Expense
    {
        $expense->update($data);

        return $expense->fresh();
    }

    public function delete(Expense $expense): bool
    {
        return (bool) $expense->delete();
    }

    public function totalByPeriod(?string $from = null, ?string $to = null): float
    {
        return (float) Expense::query()
            ->when($from, fn ($q) => $q->whereDate('expense_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('expense_date', '<=', $to))
            ->sum('amount');
    }
}
