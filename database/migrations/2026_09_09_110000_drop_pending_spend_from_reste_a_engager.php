<?php

use App\Models\CalculationRule;
use App\Services\Calculation\MetricCatalog;
use App\Services\Calculation\MetricRegistry;
use Illuminate\Database\Migrations\Migration;

/**
 * Pending expenditure (dépenses en instance) is not in the TDR;
 * reste_à_engager uses only unengaged lot buckets.
 */
return new class extends Migration
{
    public function up(): void
    {
        $rule = collect(MetricCatalog::rules())->firstWhere('key', 'reste_a_engager');

        if ($rule === null) {
            return;
        }

        $variables = MetricCatalog::variables();
        $used = [];
        foreach (array_keys($variables) as $name) {
            if (preg_match('/\b'.preg_quote($name, '/').'\b/', $rule['expression'])) {
                $used[] = ['key' => $name] + $variables[$name];
            }
        }

        CalculationRule::where('key', 'reste_a_engager')->update([
            'label_fr' => $rule['label_fr'],
            'expression' => $rule['expression'],
            'variables' => $used,
        ]);

        app(MetricRegistry::class)->flush();
    }

    public function down(): void
    {
        CalculationRule::where('key', 'reste_a_engager')->update([
            'label_fr' => 'Reste à engager (lots non engagés + dépenses en instance)',
            'expression' => 'total_1 + total_2 + total_4',
        ]);

        app(MetricRegistry::class)->flush();
    }
};
