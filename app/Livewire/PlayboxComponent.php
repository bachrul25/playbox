<?php

namespace App\Livewire;

use App\Livewire\Concerns\HasRoleGuard;
use App\Models\Playbox;
use App\Models\User;
use App\Repositories\PartnerRepository;
use App\Repositories\PlayboxRepository;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PlayboxComponent extends Component
{
    use HasRoleGuard, WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = '';

    #[Url(as: 'tipe')]
    public string $ownershipFilter = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $code = '';

    public string $name = '';

    public string $ownership_type = Playbox::OWNERSHIP_PRIBADI;

    public ?int $partner_id = null;

    public string $location = '';

    public string $status = Playbox::STATUS_TERSEDIA;

    public string $default_price_per_hour = '0';

    public string $condition_note = '';

    public function mount(): void
    {
        $this->authorizeRoles([User::ROLE_ADMIN, User::ROLE_OWNER]);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingOwnershipFilter(): void
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
        $playbox = Playbox::findOrFail($id);
        $this->editingId = $playbox->id;
        $this->code = $playbox->code;
        $this->name = $playbox->name;
        $this->ownership_type = $playbox->ownership_type;
        $this->partner_id = $playbox->partner_id;
        $this->location = (string) $playbox->location;
        $this->status = $playbox->status;
        $this->default_price_per_hour = (string) $playbox->default_price_per_hour;
        $this->condition_note = (string) $playbox->condition_note;
        $this->showModal = true;
    }

    public function save(PlayboxRepository $repo): void
    {
        $this->authorizeRoles([User::ROLE_ADMIN]);

        $rules = [
            'code' => ['required', 'string', 'max:30', 'unique:playboxes,code'.($this->editingId ? ",{$this->editingId}" : '')],
            'name' => ['required', 'string', 'max:120'],
            'ownership_type' => ['required', 'in:pribadi,kerjasama'],
            'partner_id' => ['nullable', 'integer', 'exists:partners,id'],
            'location' => ['nullable', 'string', 'max:160'],
            'status' => ['required', 'in:tersedia,disewa,maintenance,tidak_aktif'],
            'default_price_per_hour' => ['required', 'numeric', 'min:0'],
            'condition_note' => ['nullable', 'string', 'max:500'],
        ];
        if ($this->ownership_type === Playbox::OWNERSHIP_KERJASAMA) {
            $rules['partner_id'] = ['required', 'integer', 'exists:partners,id'];
        }
        $data = $this->validate($rules);

        if ($this->ownership_type === Playbox::OWNERSHIP_PRIBADI) {
            $data['partner_id'] = null;
        }

        if ($this->editingId) {
            $repo->update(Playbox::findOrFail($this->editingId), $data);
            $this->dispatch('toast', type: 'success', message: 'PlayBox berhasil diperbarui.');
        } else {
            $repo->create($data);
            $this->dispatch('toast', type: 'success', message: 'PlayBox berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id, PlayboxRepository $repo): void
    {
        $this->authorizeRoles([User::ROLE_ADMIN]);
        $repo->delete(Playbox::findOrFail($id));
        $this->dispatch('toast', type: 'success', message: 'PlayBox berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->code = '';
        $this->name = '';
        $this->ownership_type = Playbox::OWNERSHIP_PRIBADI;
        $this->partner_id = null;
        $this->location = '';
        $this->status = Playbox::STATUS_TERSEDIA;
        $this->default_price_per_hour = '0';
        $this->condition_note = '';
        $this->resetErrorBag();
    }

    #[Layout('layouts.app')]
    #[Title('Data PlayBox')]
    public function render(PlayboxRepository $repo, PartnerRepository $partners)
    {
        return view('livewire.playbox-component', [
            'playboxes' => $repo->paginate($this->search, $this->statusFilter, $this->ownershipFilter),
            'partners' => $partners->active(),
        ]);
    }
}
