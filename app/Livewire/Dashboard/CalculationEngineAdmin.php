<?php

namespace App\Livewire\Dashboard;

use App\Models\CalculationParameter;
use App\Models\CalculationRule;
use App\Models\CalculationRuleRevision;
use App\Models\Project;
use App\Services\Budget\ProjectBudget;
use App\Services\Calculation\CalculationEngine;
use App\Services\Calculation\MetricCatalog;
use App\Services\Calculation\MetricRegistry;
use App\Services\Calculation\ProjectVariableResolver;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

/**
 * The page where the ministry edits how its own numbers are computed.
 *
 * Nothing is saved until the expression parses, resolves only known variables
 * and survives a dry run, and every accepted change is both versioned and
 * previewed against a real operation first.
 */
#[Layout('layouts.dashboard')]
class CalculationEngineAdmin extends Component
{
    public function title(): string
    {
        return __('ui.calc.title');
    }
    public string $tab = 'rules';

    public ?int $editingRuleId = null;

    // Editable copy of the selected rule.
    public string $expression = '';

    public string $labelFr = '';

    public int $precision = 2;

    public ?string $clampMin = null;

    public ?string $clampMax = null;

    public bool $isActive = true;

    /** @var list<string> */
    public array $validationErrors = [];

    /** Project the preview runs against. */
    public ?int $previewProjectId = null;

    public ?float $previewCurrent = null;

    public ?float $previewNew = null;

    public ?string $previewError = null;

    /** @var array<string, float> */
    public array $parameterValues = [];

    /** Live progress of the portfolio recompute a save kicks off. */
    public ?array $recalculation = null;

    public function mount(): void
    {
        $this->authorize('viewAny', CalculationRule::class);

        $this->previewProjectId = Project::visibleTo(auth()->user())->value('id');
        $this->loadParameters();
    }

    #[On('echo-private:calculation-engine,.portfolio.recalculation')]
    public function onRecalculationProgress(array $payload): void
    {
        $this->recalculation = $payload;
    }

    // -----------------------------------------------------------------
    // Rules
    // -----------------------------------------------------------------

    public function editRule(int $ruleId): void
    {
        $rule = CalculationRule::findOrFail($ruleId);
        $this->authorize('view', $rule);

        $this->editingRuleId = $rule->id;
        $this->expression = $rule->expression;
        $this->labelFr = $rule->label_fr;
        $this->precision = $rule->precision;
        $this->clampMin = $rule->clamp_min === null ? null : (string) $rule->clamp_min;
        $this->clampMax = $rule->clamp_max === null ? null : (string) $rule->clamp_max;
        $this->isActive = $rule->is_active;

        $this->validationErrors = [];
        $this->refreshPreview();
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingRuleId', 'expression', 'labelFr', 'validationErrors', 'previewCurrent', 'previewNew', 'previewError']);
    }

    /** Re-validate and re-preview on every keystroke in the expression box. */
    public function updatedExpression(): void
    {
        $this->refreshPreview();
    }

    public function refreshPreview(): void
    {
        $rule = $this->editingRule();

        if (! $rule) {
            return;
        }

        $engine = app(CalculationEngine::class);
        $this->validationErrors = $engine->validate($this->expression, $rule->key);

        $this->previewCurrent = null;
        $this->previewNew = null;
        $this->previewError = null;

        $project = $this->previewProject();

        if (! $project) {
            $this->previewError = 'Aucun projet disponible pour l\'aperçu.';

            return;
        }

        try {
            $this->previewCurrent = ProjectBudget::for($project)->get($rule->key);
        } catch (Throwable $e) {
            $this->previewError = $e->getMessage();
        }

        if ($this->validationErrors !== []) {
            return;
        }

        // Evaluate the candidate expression against the same real inputs,
        // without touching the stored rule.
        try {
            $bag = $engine->bag(app(ProjectVariableResolver::class)->resolve($project));

            foreach ($engine->identifiers($this->expression) as $identifier) {
                if (! $bag->has($identifier)) {
                    $engine->evaluate($identifier, $bag);
                }
            }

            $this->previewNew = $this->evaluateCandidate($engine, $bag, $rule);
        } catch (Throwable $e) {
            $this->previewError = $e->getMessage();
        }
    }

    public function updatedPreviewProjectId(): void
    {
        $this->refreshPreview();
    }

    public function saveRule(): void
    {
        $rule = $this->editingRule();

        if (! $rule) {
            return;
        }

        $this->authorize('update', $rule);

        $this->validate([
            'labelFr' => ['required', 'string', 'max:255'],
            'precision' => ['required', 'integer', 'min:0', 'max:6'],
            'clampMin' => ['nullable', 'numeric'],
            'clampMax' => ['nullable', 'numeric'],
        ]);

        $this->validationErrors = app(CalculationEngine::class)->validate($this->expression, $rule->key);

        if ($this->validationErrors !== []) {
            return;
        }

        $before = $rule->expression;

        DB::transaction(function () use ($rule, $before) {
            $rule->fill([
                'label_fr' => $this->labelFr,
                'expression' => $this->expression,
                'precision' => $this->precision,
                'clamp_min' => $this->clampMin === null || $this->clampMin === '' ? null : (float) $this->clampMin,
                'clamp_max' => $this->clampMax === null || $this->clampMax === '' ? null : (float) $this->clampMax,
                'is_active' => $this->isActive,
                'updated_by' => auth()->id(),
            ]);

            if ($rule->isDirty()) {
                $rule->version = $rule->version + 1;
                $rule->save();

                CalculationRuleRevision::create([
                    'target_type' => 'rule',
                    'target_key' => $rule->key,
                    'old_value' => $before,
                    'new_value' => $rule->expression,
                    'version' => $rule->version,
                    'changed_by' => auth()->id(),
                ]);
            }
        });

        app(MetricRegistry::class)->flush();
        $this->refreshPreview();

        session()->flash('calc-status', __('ui.flash.formula_saved'));
    }

    /** Puts a rule back to the expression the TDR and the Excel sheet define. */
    public function resetRuleToDefault(int $ruleId): void
    {
        $rule = CalculationRule::findOrFail($ruleId);
        $this->authorize('update', $rule);

        $default = collect(MetricCatalog::rules())->firstWhere('key', $rule->key);

        if (! $default) {
            return;
        }

        $this->editRule($ruleId);
        $this->expression = $default['expression'];
        $this->refreshPreview();
    }

    // -----------------------------------------------------------------
    // Parameters
    // -----------------------------------------------------------------

    public function loadParameters(): void
    {
        $this->parameterValues = CalculationParameter::pluck('value', 'key')
            ->map(fn ($v) => (float) $v)
            ->all();
    }

    public function saveParameter(string $key): void
    {
        $this->authorize('calculations.manage');

        $parameter = CalculationParameter::where('key', $key)->firstOrFail();
        $value = (float) ($this->parameterValues[$key] ?? $parameter->value);

        $rules = ['value' => ['required', 'numeric']];
        if ($parameter->min !== null) {
            $rules['value'][] = 'min:'.$parameter->min;
        }
        if ($parameter->max !== null) {
            $rules['value'][] = 'max:'.$parameter->max;
        }

        validator(['value' => $value], $rules, [], ['value' => $parameter->label_fr])->validate();

        if ((float) $parameter->value === $value) {
            return;
        }

        $before = (string) $parameter->value;

        DB::transaction(function () use ($parameter, $value, $before) {
            $parameter->update(['value' => $value, 'updated_by' => auth()->id()]);

            CalculationRuleRevision::create([
                'target_type' => 'parameter',
                'target_key' => $parameter->key,
                'old_value' => $before,
                'new_value' => (string) $value,
                'version' => CalculationRuleRevision::where('target_key', $parameter->key)->count() + 1,
                'changed_by' => auth()->id(),
            ]);
        });

        app(MetricRegistry::class)->flush();
        $this->refreshPreview();

        session()->flash('calc-status', __('ui.flash.param_saved'));
    }

    // -----------------------------------------------------------------

    public function render()
    {
        $rules = CalculationRule::orderBy('category')->orderBy('key')->get()->groupBy('category');
        $parameters = CalculationParameter::orderBy('category')->orderBy('key')->get()->groupBy('category');

        $rule = $this->editingRule();

        return view('livewire.dashboard.calculation-engine-admin', [
            'ruleGroups' => $rules,
            'parameterGroups' => $parameters,
            'rule' => $rule,
            'revisions' => $rule
                ? CalculationRuleRevision::with('author')
                    ->where('target_key', $rule->key)
                    ->latest('created_at')
                    ->limit(10)
                    ->get()
                : collect(),
            'projects' => Project::visibleTo(auth()->user())
                ->orderBy('title_fr')
                ->limit(200)
                ->get(['id', 'title_fr', 'fip_id']),
            'variables' => app(MetricRegistry::class)->knownIdentifiers(),
            'canManage' => auth()->user()?->hasPermission('calculations.manage') ?? false,
        ]);
    }

    private function editingRule(): ?CalculationRule
    {
        return $this->editingRuleId ? CalculationRule::find($this->editingRuleId) : null;
    }

    private function previewProject(): ?Project
    {
        return $this->previewProjectId
            ? Project::visibleTo(auth()->user())->find($this->previewProjectId)
            : null;
    }

    /**
     * Evaluates the candidate expression with the rule's own bounds applied,
     * so the preview shows exactly what would be stored.
     */
    private function evaluateCandidate(CalculationEngine $engine, $bag, CalculationRule $rule): ?float
    {
        $probe = new CalculationRule($rule->getAttributes());
        $probe->expression = $this->expression;

        $registry = app(MetricRegistry::class);
        $registry->override($probe->key, [
            'expression' => $this->expression,
            'precision' => $this->precision,
            'clamp_min' => $this->clampMin === null || $this->clampMin === '' ? null : (float) $this->clampMin,
            'clamp_max' => $this->clampMax === null || $this->clampMax === '' ? null : (float) $this->clampMax,
        ]);

        try {
            $bag->forget($probe->key);

            return $engine->evaluate($probe->key, $bag);
        } finally {
            $registry->clearOverrides();
        }
    }
}
