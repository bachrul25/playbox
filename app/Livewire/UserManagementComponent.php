<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('User Management')]
#[Layout('layouts.app')]
class UserManagementComponent extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $filterRole = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = 'kasir';

    public string $status = 'active';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:120|unique:users,email,'.($this->editingId ?? 'NULL'),
            'password' => $this->editingId ? 'nullable|string|min:6' : 'required|string|min:6',
            'role' => 'required|in:admin,owner,kasir,operator',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterRole()
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'email', 'password']);
        $this->role = 'kasir';
        $this->status = 'active';
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $u = User::findOrFail($id);
        $this->editingId = $u->id;
        $this->name = $u->name;
        $this->email = $u->email;
        $this->password = '';
        $this->role = $u->role;
        $this->status = $u->status;
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

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        if ($this->editingId) {
            User::find($this->editingId)?->update($data);
            $this->dispatch('toast', type: 'success', message: 'User diperbarui.');
        } else {
            User::create($data);
            $this->dispatch('toast', type: 'success', message: 'User ditambahkan.');
        }
        $this->showModal = false;
    }

    public function deleteUser($id): void
    {
        $id = is_array($id) ? ($id[0] ?? null) : $id;
        if (! $id) {
            return;
        }
        if ((int) $id === (int) auth()->id()) {
            $this->dispatch('toast', type: 'error', message: 'Tidak bisa menghapus akun sendiri.');

            return;
        }
        User::find($id)?->delete();
        $this->dispatch('toast', type: 'success', message: 'User dihapus.');
    }

    public function render()
    {
        return view('livewire.user-management-component', [
            'users' => User::query()
                ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%")))
                ->when($this->filterRole, fn ($q) => $q->where('role', $this->filterRole))
                ->orderBy('name')
                ->paginate(10),
        ]);
    }
}
