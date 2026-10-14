<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\AeRevaluation;
use App\Models\User;

class AeRevaluationPolicy
{
    use HandlesWilayaScope;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::AeView->value);
    }

    public function view(User $user, AeRevaluation $revaluation): bool
    {
        return $user->hasPermission(Permission::AeView->value)
            && $this->sharesWilaya($user, $revaluation->project ?? $revaluation);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::ReevaluationRequest->value);
    }

    public function approve(User $user, AeRevaluation $revaluation): bool
    {
        return $user->hasPermission(Permission::ReevaluationApprove->value)
            && in_array($revaluation->status, ['draft', 'submitted'], true);
    }
}
