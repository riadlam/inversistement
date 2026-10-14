<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\ProjectOwner;
use App\Models\User;

class ProjectOwnerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ReferenceManage->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::ReferenceManage->value);
    }

    public function update(User $user, ProjectOwner $owner): bool
    {
        return $user->hasPermission(Permission::ReferenceManage->value);
    }

    public function delete(User $user, ProjectOwner $owner): bool
    {
        return $user->hasPermission(Permission::ReferenceManage->value);
    }
}
