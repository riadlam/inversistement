<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\ProjectClosure;
use App\Models\User;

/** Fiche 06: clôture locks the operation to lecture seule. */
class ProjectClosurePolicy
{
    use HandlesWilayaScope;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ProjectsView->value);
    }

    public function view(User $user, ProjectClosure $closure): bool
    {
        return $user->hasPermission(Permission::ProjectsView->value)
            && $this->sharesWilaya($user, $closure->project ?? $closure);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::ProjectsClose->value);
    }
}
