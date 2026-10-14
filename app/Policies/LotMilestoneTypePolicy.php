<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\LotMilestoneType;
use App\Models\User;

class LotMilestoneTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ReferenceManage->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::ReferenceManage->value);
    }

    public function update(User $user, LotMilestoneType $type): bool
    {
        return $user->hasPermission(Permission::ReferenceManage->value);
    }

    public function delete(User $user, LotMilestoneType $type): bool
    {
        return $user->hasPermission(Permission::ReferenceManage->value);
    }
}
