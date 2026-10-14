<?php

namespace App\Livewire\Dashboard;

use App\Models\DesignOffice;
use App\Models\ProjectDesignMission;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class DesignOfficesAdmin extends Component
{
    public function title(): string
    {
        return __('ui.design_offices.title');
    }

    public string $search = '';

    public ?int $editingId = null;

    public string $name = '';

    public string $city = '';

    public string $phone = '';

    public string $email = '';

    public int $sort_order = 0;

    public bool $is_active = true;

    public bool $showForm = false;

    public function mount(): void
    {
        $this->authorize('viewAny', DesignOffice::class);
    }

    #[Computed]
    public function offices(): Collection
    {
        $q = DesignOffice::query()->orderBy('sort_order')->orderBy('name');

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $q->where(function ($query) use ($term) {
                $query->where('name', 'like', $term)
                    ->orWhere('city', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        return $q->get();
    }

    public function startCreate(): void
    {
        $this->authorize('create', DesignOffice::class);
        $this->resetForm();
        $this->sort_order = ((int) DesignOffice::max('sort_order')) + 10;
        $this->is_active = true;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $office = DesignOffice::findOrFail($id);
        $this->authorize('update', $office);

        $this->editingId = $office->id;
        $this->name = $office->name;
        $this->city = (string) ($office->city ?? '');
        $this->phone = (string) ($office->phone ?? '');
        $this->email = (string) ($office->email ?? '');
        $this->sort_order = (int) $office->sort_order;
        $this->is_active = (bool) $office->is_active;
        $this->showForm = true;
        $this->resetErrorBag();
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function save(): void
    {
        if ($this->editingId) {
            $office = DesignOffice::findOrFail($this->editingId);
            $this->authorize('update', $office);
        } else {
            $this->authorize('create', DesignOffice::class);
            $office = null;
        }

        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'sort_order' => 'required|integer|min:0|max:65000',
            'is_active' => 'boolean',
        ]);

        $payload = [
            'name' => $validated['name'],
            'city' => $validated['city'] !== '' ? $validated['city'] : null,
            'phone' => $validated['phone'] !== '' ? $validated['phone'] : null,
            'email' => $validated['email'] !== '' ? $validated['email'] : null,
            'sort_order' => $validated['sort_order'],
            'is_active' => $this->is_active,
        ];

        if ($office) {
            $office->update($payload);
            session()->flash('design-offices-status', __('ui.design_offices.updated'));
        } else {
            DesignOffice::create($payload);
            session()->flash('design-offices-status', __('ui.design_offices.created'));
        }

        $this->resetForm();
        $this->showForm = false;
        unset($this->offices);
    }

    public function deactivate(int $id): void
    {
        $office = DesignOffice::findOrFail($id);
        $this->authorize('update', $office);

        $office->update(['is_active' => false]);
        session()->flash('design-offices-status', __('ui.design_offices.deactivated'));
        unset($this->offices);
    }

    public function activate(int $id): void
    {
        $office = DesignOffice::findOrFail($id);
        $this->authorize('update', $office);

        $office->update(['is_active' => true]);
        session()->flash('design-offices-status', __('ui.design_offices.activated'));
        unset($this->offices);
    }

    public function delete(int $id): void
    {
        $office = DesignOffice::findOrFail($id);
        $this->authorize('delete', $office);

        if (ProjectDesignMission::where('design_office_id', $office->id)->exists()) {
            session()->flash('design-offices-error', __('ui.design_offices.delete_blocked'));

            return;
        }

        $office->delete();
        session()->flash('design-offices-status', __('ui.design_offices.deleted'));
        unset($this->offices);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->city = '';
        $this->phone = '';
        $this->email = '';
        $this->sort_order = 0;
        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.dashboard.design-offices-admin');
    }
}
