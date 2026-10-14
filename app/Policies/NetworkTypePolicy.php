<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\NetworkType;
use App\Models\User;

class NetworkTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ReferenceManage->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::ReferenceManage->value);
    }

    public function update(User $user, NetworkType $type): bool
    {
        return $user->hasPermission(Permission::ReferenceManage->value);
    }

    public function delete(User $user, NetworkType $type): bool
    {
        return $user->hasPermission(Permission::ReferenceManage->value);
    }
}
