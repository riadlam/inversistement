<?php

namespace App\Livewire\Dashboard;

use App\Enums\Permission;
use App\Models\Permission as PermissionModel;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class UserProfile extends Component
{
    public function title(): string
    {
        return __('ui.profile.title');
    }
    #[Computed]
    public function initials(): string
    {
        $name = (string) (auth()->user()?->name ?? '');

        $initials = collect(preg_split('/[\s\-_]+/', $name, -1, PREG_SPLIT_NO_EMPTY))
            ->take(2)
            ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : '?';
    }

    /**
     * The signed-in role's permissions, grouped by category, with the full
     * catalog shown so a user can see what they do *not* have.
     *
     * @return array<string, list<array{label: string, granted: bool}>>
     */
    #[Computed]
    public function permissionMatrix(): array
    {
        $granted = array_flip(auth()->user()?->permissions() ?? []);

        $labels = PermissionModel::pluck('label_fr', 'key');

        $grouped = [];
        foreach (Permission::cases() as $permission) {
            $grouped[$permission->category()][] = [
                'label' => $labels[$permission->value] ?? $permission->label(),
                'granted' => isset($granted[$permission->value]),
            ];
        }

        return $grouped;
    }

    #[Computed]
    public function grantedCount(): int
    {
        return count(auth()->user()?->permissions() ?? []);
    }

    public function render()
    {
        return view('livewire.dashboard.user-profile');
    }
}
