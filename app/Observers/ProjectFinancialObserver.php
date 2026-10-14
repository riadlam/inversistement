<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

/**
 * Observes the models that carry a `project_id` directly: CP payments,
 * AE engagements, réévaluations, situations physiques, lots and dépenses
 * en instance.
 */
class ProjectFinancialObserver extends TriggersRecalculation
{
    protected function projectId(Model $model): ?int
    {
        return $model->project_id ? (int) $model->project_id : null;
    }
}
