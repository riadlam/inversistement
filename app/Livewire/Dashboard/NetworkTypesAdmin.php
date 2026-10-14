<?php

namespace App\Livewire\Dashboard;

use App\Models\NetworkType;
use App\Models\ProjectNetworkLink;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class NetworkTypesAdmin extends Component
{
    public function title(): string
    {
        return __('ui.networks_admin.title');
    }

    public string $search = '';

    public ?int $editingId = null;

    public string $code = '';

    public string $label_fr = '';

    public string $label_en = '';

    public int $sort_order = 0;

    public bool $is_active = true;

    public bool $showForm = false;

    public function mount(): void
    {
        $this->authorize('viewAny', NetworkType::class);
    }

    #[Computed]
    public function types(): Collection
    {
        $q = NetworkType::query()->orderBy('sort_order')->orderBy('id');

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $q->where(function ($query) use ($term) {
                $query->where('label_fr', 'like', $term)
                    ->orWhere('label_en', 'like', $term)
                    ->orWhere('code', 'like', $term);
            });
        }

        return $q->get();
    }

    public function startCreate(): void
    {
        $this->authorize('create', NetworkType::class);
        $this->resetForm();
        $this->sort_order = ((int) NetworkType::max('sort_order')) + 10;
        $this->is_active = true;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $type = NetworkType::findOrFail($id);
        $this->authorize('update', $type);

        $this->editingId = $type->id;
        $this->code = $type->code;
        $this->label_fr = $type->label_fr;
        $this->label_en = $type->label_en;
        $this->sort_order = (int) $type->sort_order;
        $this->is_active = (bool) $type->is_active;
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
            $type = NetworkType::findOrFail($this->editingId);
            $this->authorize('update', $type);
        } else {
            $this->authorize('create', NetworkType::class);
            $type = null;
        }

        $validated = $this->validate([
            'label_fr' => 'required|string|max:255',
            'label_en' => 'required|string|max:255',
            'sort_order' => 'required|integer|min:0|max:65000',
            'is_active' => 'boolean',
            'code' => [
                Rule::requiredIf(fn () => $this->editingId !== null),
                'nullable',
                'string',
                'max:80',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('network_types', 'code')->ignore($this->editingId),
            ],
        ]);

        if ($type) {
            $type->update([
                'label_fr' => $validated['label_fr'],
                'label_en' => $validated['label_en'],
                'sort_order' => $validated['sort_order'],
                'is_active' => $this->is_active,
            ]);
            session()->flash('networks-admin-status', __('ui.networks_admin.updated'));
        } else {
            $code = filled($this->code)
                ? $this->code
                : NetworkType::makeCode($validated['label_en'] ?: $validated['label_fr']);

            NetworkType::create([
                'code' => $code,
                'label_fr' => $validated['label_fr'],
                'label_en' => $validated['label_en'],
                'sort_order' => $validated['sort_order'],
                'is_active' => $this->is_active,
            ]);
            session()->flash('networks-admin-status', __('ui.networks_admin.created'));
        }

        $this->resetForm();
        $this->showForm = false;
        unset($this->types);
    }

    public function deactivate(int $id): void
    {
        $type = NetworkType::findOrFail($id);
        $this->authorize('update', $type);
        $type->update(['is_active' => false]);
        session()->flash('networks-admin-status', __('ui.networks_admin.deactivated'));
        unset($this->types);
    }

    public function activate(int $id): void
    {
        $type = NetworkType::findOrFail($id);
        $this->authorize('update', $type);
        $type->update(['is_active' => true]);
        session()->flash('networks-admin-status', __('ui.networks_admin.activated'));
        unset($this->types);
    }

    public function delete(int $id): void
    {
        $type = NetworkType::findOrFail($id);
        $this->authorize('delete', $type);

        if (ProjectNetworkLink::where('network', $type->code)->exists()) {
            session()->flash('networks-admin-error', __('ui.networks_admin.delete_blocked'));

            return;
        }

        $type->delete();
        session()->flash('networks-admin-status', __('ui.networks_admin.deleted'));
        unset($this->types);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->code = '';
        $this->label_fr = '';
        $this->label_en = '';
        $this->sort_order = 0;
        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.dashboard.network-types-admin');
    }
}
