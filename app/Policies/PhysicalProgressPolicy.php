<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\PhysicalProgress;
use App\Models\User;

class PhysicalProgressPolicy
{
    use HandlesWilayaScope;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ProjectsView->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::SituationsCreate->value);
    }

    /** Accept or decline a situation reported from the field. */
    public function validateEntry(User $user, PhysicalProgress $entry): bool
    {
        return $user->hasPermission(Permission::SituationsValidate->value)
            && $entry->status === 'pending';
    }
}
