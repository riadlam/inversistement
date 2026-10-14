<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\ProjectLot;
use App\Models\User;

class ProjectLotPolicy
{
    use HandlesWilayaScope;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::LotsView->value);
    }

    public function view(User $user, ProjectLot $lot): bool
    {
        return $user->hasPermission(Permission::LotsView->value)
            && $this->sharesWilaya($user, $lot->project ?? $lot);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::LotsManage->value);
    }

    public function update(User $user, ProjectLot $lot): bool
    {
        $project = $lot->project;

        return $user->hasPermission(Permission::LotsManage->value)
            && $this->sharesWilaya($user, $project ?? $lot)
            && ($project === null || ! $project->isLocked());
    }

    public function delete(User $user, ProjectLot $lot): bool
    {
        return $this->update($user, $lot);
    }
}
