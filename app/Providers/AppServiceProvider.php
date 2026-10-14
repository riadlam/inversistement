<?php

namespace App\Providers;

use App\Models\AeEngagement;
use App\Models\AeRevaluation;
use App\Models\CalculationParameter;
use App\Models\CalculationRule;
use App\Models\CpPayment;
use App\Models\LotMilestone;
use App\Models\Movement;
use App\Models\PhysicalProgress;
use App\Models\Project;
use App\Models\ProjectClosure;
use App\Models\ProjectLot;
use App\Observers\AuditObserver;
use App\Observers\CalculationDefinitionObserver;
use App\Observers\LotMilestoneObserver;
use App\Observers\MovementObserver;
use App\Observers\ProjectFinancialObserver;
use App\Observers\ProjectObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Every model whose writes move a project's numbers. Each one queues a
     * recompute, which persists the SPU snapshot and broadcasts over Reverb.
     */
    private const RECALCULATING_MODELS = [
        CpPayment::class,
        AeEngagement::class,
        AeRevaluation::class,
        PhysicalProgress::class,
        ProjectLot::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        foreach (self::RECALCULATING_MODELS as $model) {
            $model::observe(ProjectFinancialObserver::class);
        }

        Project::observe(ProjectObserver::class);
        LotMilestone::observe(LotMilestoneObserver::class);
        Movement::observe(MovementObserver::class);

        // Editing a formula restates the whole portfolio.
        CalculationRule::observe(CalculationDefinitionObserver::class);
        CalculationParameter::observe(CalculationDefinitionObserver::class);

        foreach ([
            Project::class,
            ProjectLot::class,
            LotMilestone::class,
            AeEngagement::class,
            AeRevaluation::class,
            CpPayment::class,
            PhysicalProgress::class,
            Movement::class,
            ProjectClosure::class,
            CalculationRule::class,
            CalculationParameter::class,
        ] as $audited) {
            $audited::observe(AuditObserver::class);
        }
    }
}
