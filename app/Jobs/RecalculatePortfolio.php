<?php

namespace App\Jobs;

use App\Events\PortfolioRecalculationProgressed;
use App\Models\Project;
use App\Services\Alerts\AlertEvaluator;
use App\Services\Calculation\MetricRegistry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Recomputes every operation after a formula or a threshold changed.
 *
 * Runs the per-project job inline rather than queueing thousands of children:
 * the work is CPU-bound and short, and doing it here keeps the progress
 * counter honest.
 */
class RecalculatePortfolio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** A whole-portfolio pass is worth a generous ceiling. */
    public int $timeout = 900;

    public function __construct(
        public readonly string $reason,
        public readonly string $batchId = '',
    ) {}

    public function handle(AlertEvaluator $evaluator, MetricRegistry $registry): void
    {
        // The rules just changed; evaluate against the new ones.
        $registry->flush();

        $batchId = $this->batchId ?: (string) Str::uuid();
        $total = Project::count();
        $processed = 0;

        event(new PortfolioRecalculationProgressed($batchId, 0, $total, $this->reason));

        Project::select('id')->chunkById(100, function ($projects) use ($evaluator, $batchId, $total, &$processed) {
            foreach ($projects as $project) {
                (new RecalculateProjectMetrics($project->id))->handle($evaluator);
                $processed++;
            }

            event(new PortfolioRecalculationProgressed($batchId, $processed, $total, $this->reason));
        });

        event(new PortfolioRecalculationProgressed($batchId, $processed, $total, $this->reason, finished: true));
    }
}
