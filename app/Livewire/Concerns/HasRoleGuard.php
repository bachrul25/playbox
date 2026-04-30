<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;

trait HasRoleGuard
{
    /**
     * Authorize roles, abort 403 if not allowed.
     */
    protected function authorizeRoles(array $roles): void
    {
        $user = Auth::user();
        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
    }
}
