<?php

namespace App\Services\Budget;

use App\Models\Project;
use App\Services\Calculation\CalculationEngine;
use App\Services\Calculation\MetricRegistry;
use App\Services\Calculation\ProjectVariableResolver;
use App\Services\Calculation\VariableBag;
use App\Services\Fiche\LotBuckets;

/**
 * The single entry point for "what are this project's numbers".
 *
 * It holds no arithmetic of its own: every value comes from an expression in
 * `calculation_rules`, so changing a formula in the admin page changes what
 * the dashboards, the fiche PDF and the DGB export all report.
 */
class ProjectBudget
{
    private ?VariableBag $bag = null;

    /** @var array<string, float|null>|null */
    private ?array $metrics = null;

    private ?LotBuckets $buckets = null;

    public function __construct(
        private readonly Project $project,
        private readonly CalculationEngine $engine,
        private readonly ProjectVariableResolver $resolver,
        private readonly MetricRegistry $registry,
    ) {}

    public static function for(Project $project): self
    {
        return new self(
            $project,
            app(CalculationEngine::class),
            app(ProjectVariableResolver::class),
            app(MetricRegistry::class),
        );
    }

    /** Raw inputs plus current parameter values. */
    public function bag(): VariableBag
    {
        return $this->bag ??= $this->engine->bag($this->resolver->resolve($this->project));
    }

    /**
     * Every active metric for this project.
     *
     * @return array<string, float|null>
     */
    public function metrics(): array
    {
        return $this->metrics ??= $this->engine->evaluateAll($this->bag());
    }

    public function get(string $key, float $default = 0.0): float
    {
        $metrics = $this->metrics();

        if (array_key_exists($key, $metrics)) {
            return $metrics[$key] ?? $default;
        }

        // Not a rule: it may be one of the raw inputs.
        $bag = $this->bag();

        return $bag->has($key) ? (float) $bag->get($key) : $default;
    }

    // -----------------------------------------------------------------
    // Named accessors for the values the documents and dashboards need.
    // -----------------------------------------------------------------

    public function apInitiale(): float
    {
        return $this->get('ap_initiale');
    }

    public function apActuelle(): float
    {
        return $this->get('ap_actuelle');
    }

    public function totalReevaluations(): float
    {
        return $this->get('total_reevaluations');
    }

    public function cumulEngagements(): float
    {
        return $this->get('cumul_engagements');
    }

    public function cumulPaiements(): float
    {
        return $this->get('cumul_paiements');
    }

    /** Programme en cours: AP actuelle moins les paiements (canevas DGB, col. U). */
    public function pec(): float
    {
        return $this->get('pec');
    }

    public function rar(): float
    {
        return $this->get('rar');
    }

    public function tauxFinancier(): float
    {
        return $this->get('taux_financier');
    }

    public function tauxPhysique(): float
    {
        return $this->get('physique_pct');
    }

    public function reliquatAe(): float
    {
        return $this->get('reliquat_ae');
    }

    public function resteAEngager(): float
    {
        return $this->get('reste_a_engager');
    }

    public function lotBuckets(): LotBuckets
    {
        return $this->buckets ??= LotBuckets::for($this->project);
    }

    public function spu(): float
    {
        return $this->get('spu_total');
    }

    /** @return array{cp: float, physical: float, delays: float, file: float} */
    public function spuBreakdown(): array
    {
        return [
            'cp' => $this->get('spu_cp'),
            'physical' => $this->get('spu_physique'),
            'delays' => $this->get('spu_delais'),
            'file' => $this->get('spu_conformite'),
        ];
    }

    /**
     * Traffic light from the SPU bands (TDR §7.3), using the configurable
     * thresholds rather than hardcoded 80 / 50.
     */
    public function trafficLight(): string
    {
        $parameters = $this->registry->parameters();
        $green = (float) ($parameters['seuil_spu_vert'] ?? 80);
        $orange = (float) ($parameters['seuil_spu_orange'] ?? 50);

        $spu = $this->spu();

        return match (true) {
            $spu >= $green => 'green',
            $spu >= $orange => 'orange',
            default => 'red',
        };
    }

    /**
     * Metric values grouped for display, with each rule's label and unit.
     *
     * @return array<string, list<array{key: string, label: string, unit: string, value: float|null}>>
     */
    public function grouped(): array
    {
        $rules = $this->registry->rules();
        $metrics = $this->metrics();

        $out = [];
        foreach ($rules as $key => $rule) {
            $out[$rule['category']][] = [
                'key' => $key,
                'label' => $rule['label_fr'],
                'unit' => $rule['unit'],
                'value' => $metrics[$key] ?? null,
            ];
        }

        return $out;
    }
}
