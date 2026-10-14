<?php

namespace Database\Seeders;

use App\Models\CalculationParameter;
use App\Models\CalculationRule;
use App\Services\Calculation\MetricCatalog;
use App\Services\Calculation\MetricRegistry;
use Illuminate\Database\Seeder;

/**
 * Loads the default formula set. Re-running restores the shipped defaults,
 * which doubles as the "réinitialiser" action in the admin page.
 */
class CalculationRuleSeeder extends Seeder
{
    public function run(): void
    {
        $variables = MetricCatalog::variables();

        foreach (MetricCatalog::rules() as $rule) {
            // Record which declared inputs this expression actually reads, so
            // the admin UI can show them without re-parsing.
            $used = [];
            foreach (array_keys($variables) as $name) {
                if (preg_match('/\b'.preg_quote($name, '/').'\b/', $rule['expression'])) {
                    $used[] = ['key' => $name] + $variables[$name];
                }
            }

            CalculationRule::updateOrCreate(
                ['key' => $rule['key']],
                [
                    'label_fr' => $rule['label_fr'],
                    'category' => $rule['category'],
                    'expression' => $rule['expression'],
                    'variables' => $used,
                    'unit' => $rule['unit'] ?? 'number',
                    'precision' => $rule['precision'] ?? 2,
                    'clamp_min' => $rule['clamp_min'] ?? null,
                    'clamp_max' => $rule['clamp_max'] ?? null,
                    'is_active' => true,
                ],
            );
        }

        foreach (MetricCatalog::parameters() as $param) {
            CalculationParameter::updateOrCreate(
                ['key' => $param['key']],
                [
                    'label_fr' => $param['label_fr'],
                    'category' => $param['category'],
                    'value' => $param['value'],
                    'type' => $param['type'],
                    'min' => $param['min'] ?? null,
                    'max' => $param['max'] ?? null,
                    'unit' => $param['unit'] ?? null,
                ],
            );
        }

        app(MetricRegistry::class)->flush();
    }
}
