<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Project;
use App\Models\User;

/**
 * Encodes the TDR RACI split: a gestionnaire writes situations but cannot
 * arbitrate, a responsable de programme arbitrates but does not create, and a
 * contrôleur budgétaire visas compliance without approving.
 */
class ProjectPolicy
{
    use HandlesWilayaScope;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permission::ProjectsView->value);
    }

    public function view(User $user, Project $project): bool
    {
        return $user->hasPermission(Permission::ProjectsView->value)
            && $this->sharesWilaya($user, $project);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(Permission::ProjectsCreate->value);
    }

    public function update(User $user, Project $project): bool
    {
        if (! $user->hasPermission(Permission::ProjectsUpdate->value)) {
            return false;
        }

        if (! $this->sharesWilaya($user, $project)) {
            return false;
        }

        if ($project->isLocked()) {
            return false;
        }

        // Maturation dossier only (create/edit form): title, category, AE/CP, checklist…
        // After approval, identity data is frozen — work continues on the fiche / CP.
        return in_array($project->status, ['draft', 'maturation'], true);
    }

    /**
     * Phase 3+ field work: lots, land, MOE, networks, revaluation/movement requests.
     * Not the create/edit maturation form.
     */
    public function updateExecution(User $user, Project $project): bool
    {
        if (! $user->hasPermission(Permission::ProjectsUpdate->value)) {
            return false;
        }

        if (! $this->sharesWilaya($user, $project)) {
            return false;
        }

        if ($project->isLocked()) {
            return false;
        }

        return in_array($project->status, ['approved', 'in_execution', 'completed'], true);
    }

    public function delete(User $user, Project $project): bool
    {
        if (! $user->hasPermission(Permission::ProjectsDelete->value)) {
            return false;
        }

        if (! $this->sharesWilaya($user, $project)) {
            return false;
        }

        // Once an operation leaves the drafting stage it is cancelled through a
        // fiche de mouvement, never deleted.
        return in_array($project->status, ['draft', 'maturation'], true);
    }

    public function submit(User $user, Project $project): bool
    {
        return $user->hasPermission(Permission::ProjectsSubmit->value)
            && $this->sharesWilaya($user, $project)
            && in_array($project->status, ['draft', 'maturation'], true);
    }

    public function arbitrate(User $user, Project $project): bool
    {
        return $user->hasPermission(Permission::ProjectsArbitrate->value)
            && in_array($project->status, ['submitted', 'in_arbitration'], true);
    }

    public function visa(User $user, Project $project): bool
    {
        return $user->hasPermission(Permission::ProjectsVisa->value)
            && $project->status === 'pending_visa';
    }

    /**
     * Ordonnateur: record a legal AE engagement against an authorized operation.
     * Not a dossier gate — only after budget visa (approved / in execution).
     */
    public function engageAe(User $user, Project $project): bool
    {
        if (! $user->hasPermission(Permission::AeEngage->value)) {
            return false;
        }

        if (! $this->sharesWilaya($user, $project)) {
            return false;
        }

        if ($project->isLocked()) {
            return false;
        }

        return in_array($project->status, ['approved', 'in_execution', 'completed'], true);
    }

    public function close(User $user, Project $project): bool
    {
        return $user->hasPermission(Permission::ProjectsClose->value)
            && ! $project->isLocked();
    }

    public function export(User $user, Project $project): bool
    {
        return $user->hasPermission(Permission::ReportsExport->value)
            && $this->sharesWilaya($user, $project);
    }
}
