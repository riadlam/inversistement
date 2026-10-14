<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\CpPayment;
use App\Models\User;

class CpPaymentPolicy
{
    use HandlesWilayaScope;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::CpView->value);
    }

    public function view(User $user, CpPayment $payment): bool
    {
        return $user->hasPermission(Permission::CpView->value)
            && $this->sharesWilaya($user, $payment->project ?? $payment);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::CpRequest->value);
    }

    /** Accept or reject a pending CP request — contrôleur budgétaire / ordonnateur. */
    public function validatePayment(User $user, CpPayment $payment): bool
    {
        if (! $user->hasPermission(Permission::CpValidate->value)) {
            return false;
        }

        if (($payment->status ?? 'pending') !== 'pending') {
            return false;
        }

        $project = $payment->relationLoaded('project')
            ? $payment->project
            : $payment->project()->first();

        return $project !== null && $this->sharesWilaya($user, $project);
    }
}
