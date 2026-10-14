<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\DesignOffice;
use App\Models\User;

class DesignOfficePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ReferenceManage->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::ReferenceManage->value);
    }

    public function update(User $user, DesignOffice $office): bool
    {
        return $user->hasPermission(Permission::ReferenceManage->value);
    }

    public function delete(User $user, DesignOffice $office): bool
    {
        return $user->hasPermission(Permission::ReferenceManage->value);
    }
}
