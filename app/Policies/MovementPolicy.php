<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Movement;
use App\Models\User;

/**
 * Fiche 05: a gestionnaire requests a cancellation or transfer; only the
 * responsable de programme validates it. A cancellation is refused if the
 * source operation already has engagements.
 */
class MovementPolicy
{
    use HandlesWilayaScope;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::AeView->value)
            || $user->hasPermission(Permission::MovementRequest->value)
            || $user->hasPermission(Permission::MovementApprove->value);
    }

    public function view(User $user, Movement $movement): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        return $this->sharesWilaya($user, $movement->sourceProject ?? $movement, 'wilaya_id');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::MovementRequest->value);
    }

    public function approve(User $user, Movement $movement): bool
    {
        return $user->hasPermission(Permission::MovementApprove->value)
            && in_array($movement->status, ['draft', 'submitted'], true);
    }
}
