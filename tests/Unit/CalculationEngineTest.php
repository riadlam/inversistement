<?php

namespace Tests\Unit;

use App\Services\Calculation\CalculationEngine;
use Tests\TestCase;

class CalculationEngineTest extends TestCase
{
    public function test_ap_actuelle_is_initiale_plus_reevaluations(): void
    {
        $engine = app(CalculationEngine::class);
        $bag = $engine->bag([
            'ap_initiale' => 80_000_000,
            'total_reevaluations' => 80_000_000,
            'cumul_paiements' => 10_000_000,
            'cumul_engagements' => 20_000_000,
            'cp_notifies' => 40_000_000,
            'cp_mandates' => 10_000_000,
            'cp_consommes' => 10_000_000,
            'ae_ouvertes' => 160_000_000,
            'physique_pct' => 60,
            'dossier_complet' => 1,
            'total_1' => 56_199_575.02,
            'total_2' => 21_272_357.50,
            'total_3' => 32_309_823.95,
            'total_receptionne' => 70_084_580.70,
        ]);

        $this->assertEquals(160_000_000.0, $engine->evaluate('ap_actuelle', $bag));
        $this->assertEquals(150_000_000.0, $engine->evaluate('pec', $bag));
        $this->assertEquals(150_000_000.0, $engine->evaluate('rar', $bag));
        $this->assertEqualsWithDelta(6.25, $engine->evaluate('taux_financier', $bag), 0.1);
    }

    public function test_superficie_converts_hectares_ares_centiares(): void
    {
        $engine = app(CalculationEngine::class);
        $bag = $engine->bag([
            'superficie_ha' => 11,
            'superficie_a' => 94,
            'superficie_ca' => 42,
        ]);

        $this->assertEquals(119442.0, $engine->evaluate('superficie_m2', $bag));
    }

    public function test_validate_rejects_unknown_variables_and_self_reference(): void
    {
        $engine = app(CalculationEngine::class);

        $this->assertNotEmpty($engine->validate('inconnu + 1'));
        $this->assertNotEmpty($engine->validate('ap_actuelle', 'ap_actuelle'));
        $this->assertEmpty($engine->validate('ap_initiale + total_reevaluations', 'ap_actuelle'));
    }

    public function test_division_by_zero_returns_zero(): void
    {
        $engine = app(CalculationEngine::class);
        $bag = $engine->bag([
            'ap_initiale' => 0,
            'total_reevaluations' => 0,
            'cumul_paiements' => 10,
        ]);

        $this->assertEquals(0.0, $engine->evaluate('taux_financier', $bag));
    }
}
