<?php

namespace App\Policies;

use App\Models\User;

/**
 * Shared wilaya check for policies. National roles see everything; every other
 * role is confined to its own wilaya, per the TDR access rules.
 */
trait HandlesWilayaScope
{
    protected function sharesWilaya(User $user, mixed $model, string $column = 'wilaya_id'): bool
    {
        if ($user->isNational()) {
            return true;
        }

        if ($user->wilaya_id === null) {
            return false;
        }

        return (int) ($model->{$column} ?? 0) === (int) $user->wilaya_id;
    }
}
