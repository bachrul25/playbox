<?php

namespace App\Repositories;

use App\Models\Playbox;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PlayboxRepository
{
    public function paginate(string $search = '', string $status = '', string $ownership = '', int $perPage = 10): LengthAwarePaginator
    {
        return Playbox::query()
            ->with('partner')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%")
                        ->orWhere('location', 'like', "%$search%");
                });
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($ownership !== '', fn ($q) => $q->where('ownership_type', $ownership))
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(array $data): Playbox
    {
        return Playbox::create($data);
    }

    public function update(Playbox $playbox, array $data): Playbox
    {
        $playbox->update($data);

        return $playbox->fresh();
    }

    public function delete(Playbox $playbox): bool
    {
        return (bool) $playbox->delete();
    }

    public function listAvailable(): Collection
    {
        return Playbox::query()
            ->where('status', '!=', Playbox::STATUS_TIDAK_AKTIF)
            ->orderBy('code')
            ->get();
    }
}
