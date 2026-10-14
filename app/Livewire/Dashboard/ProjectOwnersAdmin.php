<?php

namespace App\Livewire\Dashboard;

use App\Models\Project;
use App\Models\ProjectOwner;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class ProjectOwnersAdmin extends Component
{
    public function title(): string
    {
        return __('ui.holdings.title');
    }

    public string $search = '';

    public ?int $editingId = null;

    public string $name = '';

    public string $company_name = '';

    public string $email = '';

    public string $nif = '';

    public string $business_register = '';

    public bool $showForm = false;

    public function mount(): void
    {
        $this->authorize('viewAny', ProjectOwner::class);
    }

    #[Computed]
    public function owners(): Collection
    {
        $q = ProjectOwner::query()
            ->orderByRaw('COALESCE(NULLIF(company_name, ""), name) asc');

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $q->where(function ($query) use ($term) {
                $query->where('company_name', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('nif', 'like', $term)
                    ->orWhere('business_register', 'like', $term);
            });
        }

        return $q->get();
    }

    public function startCreate(): void
    {
        $this->authorize('create', ProjectOwner::class);
        $this->resetForm();
        $this->showForm = true;
        $this->editingId = null;
    }

    public function edit(int $id): void
    {
        $owner = ProjectOwner::findOrFail($id);
        $this->authorize('update', $owner);

        $this->editingId = $owner->id;
        $this->name = (string) $owner->name;
        $this->company_name = (string) ($owner->company_name ?? '');
        $this->email = (string) ($owner->email ?? '');
        $this->nif = (string) ($owner->nif ?? '');
        $this->business_register = (string) ($owner->business_register ?? '');
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
            $owner = ProjectOwner::findOrFail($this->editingId);
            $this->authorize('update', $owner);
        } else {
            $this->authorize('create', ProjectOwner::class);
            $owner = null;
        }

        $emailRule = 'nullable|string|email|max:255|unique:project_owners,email';
        if ($this->editingId) {
            $emailRule .= ','.$this->editingId;
        }

        $validated = $this->validate([
            'company_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'nif' => 'nullable|string|max:50',
            'business_register' => 'nullable|string|max:50',
        ]);

        $payload = [
            'company_name' => $validated['company_name'],
            'name' => $validated['name'],
            'email' => $validated['email'] !== '' ? $validated['email'] : null,
            'nif' => $validated['nif'] !== '' ? $validated['nif'] : null,
            'business_register' => $validated['business_register'] !== '' ? $validated['business_register'] : null,
        ];

        if ($owner) {
            $owner->update($payload);
            session()->flash('holdings-status', __('ui.holdings.updated'));
        } else {
            ProjectOwner::create($payload);
            session()->flash('holdings-status', __('ui.holdings.created'));
        }

        $this->resetForm();
        $this->showForm = false;
        unset($this->owners);
    }

    public function delete(int $id): void
    {
        $owner = ProjectOwner::findOrFail($id);
        $this->authorize('delete', $owner);

        if (Project::where('project_owner_id', $owner->id)->exists()) {
            session()->flash('holdings-error', __('ui.holdings.delete_blocked'));

            return;
        }

        $owner->delete();
        session()->flash('holdings-status', __('ui.holdings.deleted'));
        unset($this->owners);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->company_name = '';
        $this->email = '';
        $this->nif = '';
        $this->business_register = '';
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.dashboard.project-owners-admin');
    }
}
