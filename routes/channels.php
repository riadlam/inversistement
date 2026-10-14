<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Channel authorization mirrors the RBAC rules rather than duplicating them:
| a subscriber must already be allowed to read the thing the channel talks
| about, so a DASS in Biskra never receives another wilaya's recomputes.
|
*/

/** Everything that happens to one operation. */
Broadcast::channel('projects.{project}', function (User $user, int $project) {
    $model = Project::withTrashed()->find($project);

    if (! $model) {
        return false;
    }

    return $user->can('view', $model)
        ? ['id' => $user->id, 'name' => $user->name, 'role' => $user->roleLabel()]
        : false;
});

/** Portfolio-wide recomputes and alerts for one wilaya. */
Broadcast::channel('portfolio.wilaya.{wilaya}', function (User $user, int $wilaya) {
    if (! $user->hasPermission('projects.view')) {
        return false;
    }

    return $user->isNational() || (int) $user->wilaya_id === $wilaya;
});

/** The national roll-up: DGB, ministre, contrôleur général, admin. */
Broadcast::channel('portfolio.national', function (User $user) {
    return $user->isNational() && $user->hasPermission('projects.view');
});

/** Progress of a portfolio-wide recompute after a formula change. */
Broadcast::channel('calculation-engine', function (User $user) {
    return $user->hasPermission('calculations.view');
});

/** A user's own notification bell. */
Broadcast::channel('users.{id}', function (User $user, int $id) {
    return (int) $user->id === $id;
});
