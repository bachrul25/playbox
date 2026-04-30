<?php

namespace App\Livewire;

use App\Livewire\Concerns\HasRoleGuard;
use App\Models\User;
use App\Repositories\PartnerRepository;
use App\Repositories\UserRepository;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UserManagementComponent extends Component
{
    use HasRoleGuard, WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'role')]
    public string $roleFilter = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = User::ROLE_ADMIN;

    public ?int $partner_id = null;

    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorizeRoles([User::ROLE_ADMIN]);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->role;
        $this->partner_id = $user->partner_id;
        $this->is_active = (bool) $user->is_active;
        $this->showModal = true;
    }

    public function save(UserRepository $repo): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:users,email'.($this->editingId ? ",{$this->editingId}" : '')],
            'role' => ['required', 'in:admin,owner,mitra'],
            'partner_id' => ['nullable', 'integer', 'exists:partners,id'],
            'is_active' => ['boolean'],
        ];
        $rules['password'] = $this->editingId
            ? ['nullable', 'string', 'min:6']
            : ['required', 'string', 'min:6'];

        $data = $this->validate($rules);

        if ($this->role !== User::ROLE_MITRA) {
            $data['partner_id'] = null;
        } elseif (empty($data['partner_id'])) {
            $this->addError('partner_id', 'Mitra wajib dipilih untuk role mitra.');

            return;
        }

        if ($this->editingId) {
            $repo->update(User::findOrFail($this->editingId), $data);
            $this->dispatch('toast', type: 'success', message: 'User berhasil diperbarui.');
        } else {
            $repo->create($data);
            $this->dispatch('toast', type: 'success', message: 'User berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id, UserRepository $repo): void
    {
        if ($id === auth()->id()) {
            $this->dispatch('toast', type: 'warning', message: 'Tidak bisa menghapus akun sendiri.');

            return;
        }
        $repo->delete(User::findOrFail($id));
        $this->dispatch('toast', type: 'success', message: 'User dihapus.');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = User::ROLE_ADMIN;
        $this->partner_id = null;
        $this->is_active = true;
        $this->resetErrorBag();
    }

    #[Layout('layouts.app')]
    #[Title('Manajemen User')]
    public function render(UserRepository $repo, PartnerRepository $partners)
    {
        return view('livewire.user-management-component', [
            'users' => $repo->paginate($this->search, $this->roleFilter),
            'partners' => $partners->active(),
        ]);
    }
}
