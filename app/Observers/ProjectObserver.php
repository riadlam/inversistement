<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

/**
 * The project itself: AP, CP notifiés, délais and ODS all feed the engine.
 */
class ProjectObserver extends TriggersRecalculation
{
    /** Columns that change a metric; anything else is not worth a recompute. */
    private const WATCHED = [
        'ae_da', 'cp_da', 'amount_committed_da', 'ods_date', 'deadline',
        'project_duration_months', 'equipment_progress_pct', 'status', 'phase',
        'cp_notification_current_year_da', 'cp_reliquat_da', 'cp_besoins_da',
    ];

    protected function projectId(Model $model): ?int
    {
        return (int) $model->getKey();
    }

    public function saved(Model $model): void
    {
        if ($model->wasRecentlyCreated || $model->wasChanged(self::WATCHED)) {
            $this->recalculate($model);
        }
    }
}
