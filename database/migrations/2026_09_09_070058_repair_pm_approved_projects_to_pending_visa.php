<?php

use App\Enums\Role;
use App\Models\Project;
use App\Services\Workflow\ProjectWorkflowNotifier;
use Illuminate\Database\Migrations\Migration;

/**
 * Before the visa step existed, programme-manager accept wrote status=approved.
 * Those dossiers still need the budget controller queue.
 */
return new class extends Migration
{
    public function up(): void
    {
        $projects = Project::query()
            ->where('status', 'approved')
            ->whereHas('validator', fn ($q) => $q->where('role', Role::ProgrammeManager->value))
            ->get();

        $notifier = app(ProjectWorkflowNotifier::class);

        foreach ($projects as $project) {
            $project->update([
                'status' => 'pending_visa',
            ]);

            $notifier->notifyPendingVisa($project->fresh());
        }
    }

    public function down(): void
    {
        // Irreversible data repair — leave as pending_visa.
    }
};
