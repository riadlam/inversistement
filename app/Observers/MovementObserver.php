<?php

namespace App\Observers;

use App\Jobs\RecalculateProjectMetrics;
use App\Models\Movement;
use Illuminate\Support\Facades\DB;

/**
 * A movement touches two operations (source, and optionally a transfer target),
 * so both need a recompute after the row lands.
 */
class MovementObserver
{
    public function saved(Movement $movement): void
    {
        $this->recalculate($movement);
    }

    public function deleted(Movement $movement): void
    {
        $this->recalculate($movement);
    }

    private function recalculate(Movement $movement): void
    {
        $ids = array_filter([
            $movement->source_project_id,
            $movement->target_project_id,
        ]);

        DB::afterCommit(function () use ($ids) {
            foreach (array_unique($ids) as $id) {
                RecalculateProjectMetrics::dispatch((int) $id);
            }
        });
    }
}
