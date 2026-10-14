<?php

namespace App\Observers;

use App\Jobs\RecalculatePortfolio;
use App\Services\Calculation\MetricRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * A formula or a threshold changed, so every operation's numbers are stale.
 *
 * Queues one portfolio-wide pass rather than a job per project: the whole
 * point of the batch is to broadcast a single progress stream to the admin
 * page while the dashboards refresh underneath.
 */
class CalculationDefinitionObserver
{
    public function __construct(private readonly MetricRegistry $registry) {}

    public function saved(Model $model): void
    {
        // A rule toggled inactive or a parameter nudged still changes results.
        if (! $model->wasRecentlyCreated && ! $model->wasChanged()) {
            return;
        }

        $this->schedule($model);
    }

    public function deleted(Model $model): void
    {
        $this->schedule($model);
    }

    private function schedule(Model $model): void
    {
        $this->registry->flush();

        $reason = sprintf(
            '%s « %s »',
            class_basename($model) === 'CalculationRule' ? 'Formule modifiée' : 'Paramètre modifié',
            $model->label_fr ?? $model->key,
        );

        DB::afterCommit(fn () => RecalculatePortfolio::dispatch($reason));
    }
}
