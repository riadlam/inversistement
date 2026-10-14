<?php

namespace App\Support\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Journal des événements (TDR §9 / §10): who changed what, and the before/after
 * snapshot. Callers never write to `audit_logs` directly.
 */
final class AuditLogger
{
    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public static function record(
        string $action,
        Model $entity,
        ?array $before = null,
        ?array $after = null,
        ?int $projectId = null,
    ): void {
        if (! \Illuminate\Support\Facades\Schema::hasTable('audit_logs')) {
            return;
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'project_id' => $projectId ?? self::projectIdOf($entity),
            'action' => $action,
            'entity_type' => $entity->getMorphClass(),
            'entity_id' => $entity->getKey(),
            'before' => $before,
            'after' => $after,
            'ip_address' => request()->ip(),
        ]);
    }

    private static function projectIdOf(Model $entity): ?int
    {
        if (isset($entity->project_id) && $entity->project_id) {
            return (int) $entity->project_id;
        }

        if (isset($entity->source_project_id) && $entity->source_project_id) {
            return (int) $entity->source_project_id;
        }

        if ($entity instanceof \App\Models\Project) {
            return (int) $entity->id;
        }

        return null;
    }
}
