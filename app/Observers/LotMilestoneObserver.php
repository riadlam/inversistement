<?php

namespace App\Observers;

use App\Models\LotMilestone;
use Illuminate\Database\Eloquent\Model;

/** Milestones reach their project through the lot they belong to. */
class LotMilestoneObserver extends TriggersRecalculation
{
    protected function projectId(Model $model): ?int
    {
        /** @var LotMilestone $model */
        $lot = $model->relationLoaded('lot') ? $model->lot : $model->lot()->first();

        return $lot?->project_id ? (int) $lot->project_id : null;
    }
}
