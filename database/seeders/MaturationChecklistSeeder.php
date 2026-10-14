<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * TDR Fiche 01 check-list: Foncier [ ], Études [ ], Devis [ ], Avis technique [ ].
 */
class MaturationChecklistSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['key' => 'maturation_land', 'label' => 'Foncier — acte de propriété ou décision d\'affectation', 'sort_order' => 1],
            ['key' => 'maturation_studies', 'label' => 'Études (APS / APD)', 'sort_order' => 2],
            ['key' => 'maturation_quote', 'label' => 'Devis estimatif et quantitatif détaillé', 'sort_order' => 3],
            ['key' => 'maturation_technical_opinion', 'label' => 'Avis technique (CTC, organismes techniques)', 'sort_order' => 4],
        ];

        foreach ($items as $item) {
            DB::table('maturation_checklist_definitions')->updateOrInsert(
                ['key' => $item['key']],
                ['label' => $item['label'], 'sort_order' => $item['sort_order']],
            );
        }
    }
}
