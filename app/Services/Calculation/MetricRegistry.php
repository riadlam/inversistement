<?php

namespace App\Services\Calculation;

use App\Models\CalculationParameter;
use App\Models\CalculationRule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Reads the active rules and parameters, falling back to {@see MetricCatalog}
 * when the tables are not seeded yet so the engine never hard-fails on a fresh
 * install.
 */
class MetricRegistry
{
    private const CACHE_RULES = 'calc.rules';

    private const CACHE_PARAMS = 'calc.parameters';

    private const CACHE_TTL = 3600;

    /** @var array<string, array<string, mixed>>|null */
    private ?array $rules = null;

    /** @var array<string, float|int|bool>|null */
    private ?array $parameters = null;

    /**
     * In-memory rule patches used by the admin live preview, so a candidate
     * expression can be evaluated without being saved.
     *
     * @var array<string, array<string, mixed>>
     */
    private array $overrides = [];

    /**
     * Active rules keyed by metric key.
     *
     * @return array<string, array<string, mixed>>
     */
    public function rules(): array
    {
        if ($this->rules !== null) {
            return $this->overrides === [] ? $this->rules : $this->applyOverrides($this->rules);
        }

        $this->rules = Cache::remember(self::CACHE_RULES, self::CACHE_TTL, function () {
            if (! Schema::hasTable('calculation_rules')) {
                return $this->rulesFromCatalog();
            }

            $rows = CalculationRule::where('is_active', true)->get();

            if ($rows->isEmpty()) {
                return $this->rulesFromCatalog();
            }

            return $rows->keyBy('key')->map(fn (CalculationRule $r) => [
                'key' => $r->key,
                'label_fr' => $r->label_fr,
                'category' => $r->category,
                'expression' => $r->expression,
                'unit' => $r->unit,
                'precision' => $r->precision,
                'clamp_min' => $r->clamp_min,
                'clamp_max' => $r->clamp_max,
            ])->all();
        });

        return $this->overrides === [] ? $this->rules : $this->applyOverrides($this->rules);
    }

    /**
     * Patch one rule for the lifetime of the current request only. The admin
     * preview uses this to evaluate an unsaved expression against real data.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function override(string $key, array $attributes): void
    {
        $this->overrides[$key] = $attributes;
    }

    public function clearOverrides(): void
    {
        $this->overrides = [];
    }

    /**
     * @param  array<string, array<string, mixed>>  $rules
     * @return array<string, array<string, mixed>>
     */
    private function applyOverrides(array $rules): array
    {
        foreach ($this->overrides as $key => $attributes) {
            $rules[$key] = array_merge($rules[$key] ?? ['key' => $key, 'category' => 'custom', 'unit' => 'number'], $attributes);
        }

        return $rules;
    }

    /** @return array<string, mixed>|null */
    public function rule(string $key): ?array
    {
        return $this->rules()[$key] ?? null;
    }

    public function hasRule(string $key): bool
    {
        return isset($this->rules()[$key]);
    }

    /**
     * Tunables, ready to be merged into a {@see VariableBag}.
     *
     * @return array<string, float|int|bool>
     */
    public function parameters(): array
    {
        if ($this->parameters !== null) {
            return $this->parameters;
        }

        $this->parameters = Cache::remember(self::CACHE_PARAMS, self::CACHE_TTL, function () {
            if (! Schema::hasTable('calculation_parameters')) {
                return $this->parametersFromCatalog();
            }

            $rows = CalculationParameter::all();

            if ($rows->isEmpty()) {
                return $this->parametersFromCatalog();
            }

            return $rows->keyBy('key')
                ->map(fn (CalculationParameter $p) => $p->typedValue())
                ->all();
        });

        return $this->parameters;
    }

    /** Every identifier an expression is allowed to reference. */
    public function knownIdentifiers(): array
    {
        return array_unique(array_merge(
            array_keys(MetricCatalog::variables()),
            array_keys($this->rules()),
            array_keys($this->parameters()),
        ));
    }

    public function flush(): void
    {
        $this->rules = null;
        $this->parameters = null;
        $this->overrides = [];
        Cache::forget(self::CACHE_RULES);
        Cache::forget(self::CACHE_PARAMS);
    }

    /** @return array<string, array<string, mixed>> */
    private function rulesFromCatalog(): array
    {
        $out = [];
        foreach (MetricCatalog::rules() as $rule) {
            $out[$rule['key']] = $rule + [
                'clamp_min' => null,
                'clamp_max' => null,
                'precision' => 2,
                'unit' => 'number',
            ];
        }

        return $out;
    }

    /** @return array<string, float|int|bool> */
    private function parametersFromCatalog(): array
    {
        $out = [];
        foreach (MetricCatalog::parameters() as $param) {
            $out[$param['key']] = match ($param['type']) {
                'int' => (int) $param['value'],
                'bool' => (bool) $param['value'],
                default => (float) $param['value'],
            };
        }

        return $out;
    }
}
