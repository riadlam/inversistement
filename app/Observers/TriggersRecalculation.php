<?php

namespace App\Observers;

use App\Jobs\RecalculateProjectMetrics;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Shared observer for every model whose writes move a project's numbers.
 *
 * The job is queued after the transaction commits, so a recompute never reads
 * a row that is about to be rolled back.
 */
abstract class TriggersRecalculation
{
    /** Resolves the project the write belongs to. */
    abstract protected function projectId(Model $model): ?int;

    public function saved(Model $model): void
    {
        $this->recalculate($model);
    }

    public function deleted(Model $model): void
    {
        $this->recalculate($model);
    }

    protected function recalculate(Model $model): void
    {
        $projectId = $this->projectId($model);

        if (! $projectId) {
            return;
        }

        DB::afterCommit(fn () => RecalculateProjectMetrics::dispatch($projectId));
    }
}
