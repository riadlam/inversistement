<?php

namespace App\Policies;

use App\Models\CalculationRule;
use App\Models\User;

/**
 * Formulas are national reference data: anyone with dashboard access may read
 * them, but only the roles holding `calculations.manage` may change how the
 * ministry's numbers are computed.
 */
class CalculationRulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('calculations.view');
    }

    public function view(User $user, CalculationRule $rule): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, CalculationRule $rule): bool
    {
        return $user->hasPermission('calculations.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('calculations.manage');
    }

    public function delete(User $user, CalculationRule $rule): bool
    {
        // Rules are deactivated, never deleted: an export made last year must
        // stay explicable.
        return false;
    }
}
