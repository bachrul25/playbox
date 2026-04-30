<?php

namespace App\Repositories;

use App\Models\Partner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PartnerRepository
{
    public function paginate(string $search = '', string $status = '', int $perPage = 10): LengthAwarePaginator
    {
        return Partner::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('cafe_name', 'like', "%$search%")
                        ->orWhere('person_in_charge', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%");
                });
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(array $data): Partner
    {
        return Partner::create($data);
    }

    public function update(Partner $partner, array $data): Partner
    {
        $partner->update($data);

        return $partner->fresh();
    }

    public function delete(Partner $partner): bool
    {
        return (bool) $partner->delete();
    }

    public function active(): Collection
    {
        return Partner::query()->where('status', 'aktif')->orderBy('cafe_name')->get();
    }
}
