<?php

namespace App\Services\Calculation;

/**
 * Canonical catalog of metrics and tunables, used to seed the database and to
 * validate expressions an administrator edits.
 *
 * Every default expression below is taken verbatim from the source documents:
 *  - Terme Technique de Référence §7 (indicateurs, SPU, feux tricolores)
 *  - "prototype tableau d'investissement (application).xlsx" (cells W15, =R-T)
 *  - the scanned FICHE DE SUIVI DU PROJET (AP initiale -> AP actuelle)
 */
final class MetricCatalog
{
    public const CATEGORY_BUDGET = 'budget';

    public const CATEGORY_SPU = 'spu';

    public const CATEGORY_PERFORMANCE = 'performance';

    public const CATEGORY_LOTS = 'lots';

    public const CATEGORY_FONCIER = 'foncier';

    /**
     * Raw inputs the engine assembles from a project. Anything not listed here
     * and not a rule key is rejected when validating an expression.
     *
     * @return array<string, array{label: string, unit: string}>
     */
    public static function variables(): array
    {
        return [
            // Money (dinars)
            'ap_initiale' => ['label' => 'AP / AE initiale', 'unit' => 'da'],
            'total_reevaluations' => ['label' => 'Total des réévaluations accordées', 'unit' => 'da'],
            'cumul_engagements' => ['label' => 'Cumul d\'engagements', 'unit' => 'da'],
            'cumul_paiements' => ['label' => 'Cumul de paiements', 'unit' => 'da'],
            'cp_notifies' => ['label' => 'CP notifiés', 'unit' => 'da'],
            'cp_mandates' => ['label' => 'CP mandatés (validés)', 'unit' => 'da'],
            'cp_consommes' => ['label' => 'CP consommés', 'unit' => 'da'],
            'ae_ouvertes' => ['label' => 'AE ouvertes', 'unit' => 'da'],

            // Execution
            'physique_pct' => ['label' => 'Taux d\'avancement physique', 'unit' => 'pct'],
            'equipement_pct' => ['label' => 'Taux d\'équipement', 'unit' => 'pct'],
            'dossier_complet' => ['label' => 'Dossier de maturation complet', 'unit' => 'bool'],

            // Time
            'delai_contractuel_jours' => ['label' => 'Délai contractuel (jours)', 'unit' => 'days'],
            'delai_reel_jours' => ['label' => 'Délai réel écoulé (jours)', 'unit' => 'days'],
            'jours_ecoules' => ['label' => 'Jours écoulés depuis l\'ODS', 'unit' => 'days'],
            'jours_restants' => ['label' => 'Jours restants avant échéance', 'unit' => 'days'],
            'jours_inscription_ods' => ['label' => 'Jours entre inscription et ODS', 'unit' => 'days'],
            'jours_depuis_situation' => ['label' => 'Jours depuis la dernière situation physique', 'unit' => 'days'],

            // Lot buckets (dinars)
            'total_receptionne' => ['label' => 'TOTAL — lots réceptionnés', 'unit' => 'da'],
            'total_3' => ['label' => 'TOTAL — lots en cours de réalisation', 'unit' => 'da'],
            'total_1' => ['label' => 'TOTAL — lots en cours de lancement', 'unit' => 'da'],
            'total_2' => ['label' => 'TOTAL — lots non lancés', 'unit' => 'da'],

            // Assiette foncière (hectares / ares / centiares, as recorded on the fiche)
            'superficie_ha' => ['label' => 'Superficie — hectares', 'unit' => 'number'],
            'superficie_a' => ['label' => 'Superficie — ares', 'unit' => 'number'],
            'superficie_ca' => ['label' => 'Superficie — centiares', 'unit' => 'number'],
        ];
    }

    /**
     * Tunable thresholds and weights, exposed to expressions as variables.
     *
     * @return list<array<string, mixed>>
     */
    public static function parameters(): array
    {
        return [
            // SPU weighting — TDR §7.3 / §12.5
            ['key' => 'poids_cp', 'label_fr' => 'SPU — points consommation des CP', 'category' => self::CATEGORY_SPU, 'value' => 30, 'type' => 'int', 'min' => 0, 'max' => 100, 'unit' => 'points'],
            ['key' => 'poids_physique', 'label_fr' => 'SPU — points avancement physique', 'category' => self::CATEGORY_SPU, 'value' => 40, 'type' => 'int', 'min' => 0, 'max' => 100, 'unit' => 'points'],
            ['key' => 'poids_delais', 'label_fr' => 'SPU — points respect des délais', 'category' => self::CATEGORY_SPU, 'value' => 20, 'type' => 'int', 'min' => 0, 'max' => 100, 'unit' => 'points'],
            ['key' => 'poids_conformite', 'label_fr' => 'SPU — points conformité documentaire', 'category' => self::CATEGORY_SPU, 'value' => 10, 'type' => 'int', 'min' => 0, 'max' => 100, 'unit' => 'points'],

            // Traffic-light bands — TDR §7.3
            ['key' => 'seuil_spu_vert', 'label_fr' => 'Seuil SPU — projet sain (vert)', 'category' => self::CATEGORY_SPU, 'value' => 80, 'type' => 'int', 'min' => 0, 'max' => 100, 'unit' => 'points'],
            ['key' => 'seuil_spu_orange', 'label_fr' => 'Seuil SPU — vigilance (orange)', 'category' => self::CATEGORY_SPU, 'value' => 50, 'type' => 'int', 'min' => 0, 'max' => 100, 'unit' => 'points'],

            // Alert thresholds — TDR §7.2 and §7.3
            ['key' => 'seuil_physique_vs_prevision', 'label_fr' => 'Alerte rouge — avancement physique sous % de la prévision', 'category' => 'alerts', 'value' => 50, 'type' => 'int', 'min' => 0, 'max' => 100, 'unit' => 'pct'],
            ['key' => 'seuil_ecart_cp_physique', 'label_fr' => 'Alerte rouge — écart CP / physique', 'category' => 'alerts', 'value' => 20, 'type' => 'int', 'min' => 0, 'max' => 100, 'unit' => 'pct'],
            ['key' => 'seuil_delai_restant_jours', 'label_fr' => 'Alerte — délai restant sans réception', 'category' => 'alerts', 'value' => 30, 'type' => 'int', 'min' => 0, 'max' => 3650, 'unit' => 'days'],
            ['key' => 'seuil_cp_epuise', 'label_fr' => 'Alerte orange — CP consommés au-delà de', 'category' => 'alerts', 'value' => 90, 'type' => 'int', 'min' => 0, 'max' => 100, 'unit' => 'pct'],
            ['key' => 'seuil_reevaluation', 'label_fr' => 'Alerte orange — réévaluation imminente au-delà de', 'category' => 'alerts', 'value' => 10, 'type' => 'int', 'min' => 0, 'max' => 100, 'unit' => 'pct'],
            ['key' => 'seuil_ecart_physique_financier', 'label_fr' => 'Vert — écart physique / financier inférieur à', 'category' => 'alerts', 'value' => 5, 'type' => 'int', 'min' => 0, 'max' => 100, 'unit' => 'pct'],
            ['key' => 'seuil_delai_maturation_jours', 'label_fr' => 'Objectif — délai moyen de mise en chantier', 'category' => 'alerts', 'value' => 90, 'type' => 'int', 'min' => 0, 'max' => 3650, 'unit' => 'days'],
            ['key' => 'seuil_chantier_arret_jours', 'label_fr' => 'Alerte rouge — jours sans situation physique', 'category' => 'alerts', 'value' => 60, 'type' => 'int', 'min' => 0, 'max' => 3650, 'unit' => 'days'],
        ];
    }

    /**
     * Metric rules. `expression` is the seeded default; an administrator may
     * replace it at runtime.
     *
     * @return list<array<string, mixed>>
     */
    public static function rules(): array
    {
        return [
            // ---------------------------------------------------------------
            // Budget — AP initiale -> AP actuelle (fiche de suivi, img905)
            // ---------------------------------------------------------------
            [
                'key' => 'ap_actuelle',
                'label_fr' => 'AP actuelle (AE après réévaluations)',
                'category' => self::CATEGORY_BUDGET,
                'expression' => 'ap_initiale + total_reevaluations',
                'unit' => 'da',
                'precision' => 2,
                'clamp_min' => 0,
            ],
            [
                // Excel column U: "Programme en cours (PEC) (4)=(1)-(3)", formula =R16-T16
                'key' => 'pec',
                'label_fr' => 'Programme en cours (PEC)',
                'category' => self::CATEGORY_BUDGET,
                'expression' => 'ap_actuelle - cumul_paiements',
                'unit' => 'da',
                'precision' => 2,
            ],
            [
                'key' => 'rar',
                'label_fr' => 'Reste à réaliser (RAR)',
                'category' => self::CATEGORY_BUDGET,
                'expression' => 'max(ap_actuelle - cumul_paiements, 0)',
                'unit' => 'da',
                'precision' => 2,
                'clamp_min' => 0,
            ],
            [
                // Excel cell W15: "cumul de paiment divisé par AE-ACTUELLE"
                'key' => 'taux_financier',
                'label_fr' => 'Taux d\'avancement financier',
                'category' => self::CATEGORY_BUDGET,
                'expression' => 'cumul_paiements / ap_actuelle * 100',
                'unit' => 'pct',
                'precision' => 1,
                'clamp_min' => 0,
                'clamp_max' => 100,
            ],
            [
                'key' => 'reliquat_ae',
                'label_fr' => 'Reliquat d\'AE à engager',
                'category' => self::CATEGORY_BUDGET,
                // TDR: AE actuelle − somme des engagements (ae_engagements only).
                'expression' => 'max(ap_actuelle - cumul_engagements, 0)',
                'unit' => 'da',
                'precision' => 2,
                'clamp_min' => 0,
            ],

            // ---------------------------------------------------------------
            // Performance — TDR §7.1
            // ---------------------------------------------------------------
            [
                'key' => 'tc_ae',
                'label_fr' => 'Taux de consommation des AE (TC-AE)',
                'category' => self::CATEGORY_PERFORMANCE,
                'expression' => 'cumul_engagements / ae_ouvertes * 100',
                'unit' => 'pct',
                'precision' => 1,
                'clamp_min' => 0,
            ],
            [
                'key' => 'tc_cp',
                'label_fr' => 'Taux de consommation des CP (TC-CP)',
                'category' => self::CATEGORY_PERFORMANCE,
                'expression' => 'cp_mandates / cp_notifies * 100',
                'unit' => 'pct',
                'precision' => 1,
                'clamp_min' => 0,
            ],
            [
                'key' => 'icr',
                'label_fr' => 'Indice de consommation relative (ICR)',
                'category' => self::CATEGORY_PERFORMANCE,
                'expression' => 'cp_mandates / cumul_engagements * 100',
                'unit' => 'pct',
                'precision' => 1,
                'clamp_min' => 0,
            ],
            [
                // Overrun relative to the contractual delay. Left unclamped
                // upwards so a badly slipped project still shows how far it is
                // out; spu_delais floors the resulting penalty at zero points.
                'key' => 'retard_pct',
                'label_fr' => 'Pourcentage de retard sur le délai contractuel',
                'category' => self::CATEGORY_PERFORMANCE,
                'expression' => 'max((delai_reel_jours - delai_contractuel_jours) / delai_contractuel_jours * 100, 0)',
                'unit' => 'pct',
                'precision' => 1,
                'clamp_min' => 0,
            ],
            [
                'key' => 'glissement_calendaire',
                'label_fr' => 'Glissement calendaire',
                'category' => self::CATEGORY_PERFORMANCE,
                'expression' => '(delai_reel_jours - delai_contractuel_jours) / delai_contractuel_jours',
                'unit' => 'number',
                'precision' => 3,
            ],
            [
                'key' => 'ecart_maturation',
                'label_fr' => 'Écart de maturation (inscription -> ODS)',
                'category' => self::CATEGORY_PERFORMANCE,
                'expression' => 'jours_inscription_ods',
                'unit' => 'days',
                'precision' => 0,
                'clamp_min' => 0,
            ],
            [
                'key' => 'taux_reevaluation',
                'label_fr' => 'Taux de réévaluation',
                'category' => self::CATEGORY_PERFORMANCE,
                'expression' => 'total_reevaluations / ap_initiale * 100',
                'unit' => 'pct',
                'precision' => 1,
                'clamp_min' => 0,
            ],
            [
                'key' => 'ecart_physique_financier',
                'label_fr' => 'Écart physique / financier',
                'category' => self::CATEGORY_PERFORMANCE,
                'expression' => 'abs(physique_pct - taux_financier)',
                'unit' => 'pct',
                'precision' => 1,
                'clamp_min' => 0,
            ],

            // ---------------------------------------------------------------
            // SPU — TDR §7.3, weights from calculation_parameters
            // ---------------------------------------------------------------
            [
                'key' => 'spu_cp',
                'label_fr' => 'SPU — performance financière',
                'category' => self::CATEGORY_SPU,
                'expression' => 'cp_consommes / cp_notifies * poids_cp',
                'unit' => 'points',
                'precision' => 2,
                'clamp_min' => 0,
            ],
            [
                'key' => 'spu_physique',
                'label_fr' => 'SPU — avancement physique',
                'category' => self::CATEGORY_SPU,
                'expression' => 'physique_pct * poids_physique / 100',
                'unit' => 'points',
                'precision' => 2,
                'clamp_min' => 0,
            ],
            [
                'key' => 'spu_delais',
                'label_fr' => 'SPU — maîtrise des délais',
                'category' => self::CATEGORY_SPU,
                'expression' => 'poids_delais - (retard_pct * poids_delais / 100)',
                'unit' => 'points',
                'precision' => 2,
                'clamp_min' => 0,
            ],
            [
                'key' => 'spu_conformite',
                'label_fr' => 'SPU — conformité administrative',
                'category' => self::CATEGORY_SPU,
                'expression' => 'dossier_complet ? poids_conformite : 0',
                'unit' => 'points',
                'precision' => 2,
                'clamp_min' => 0,
            ],
            [
                'key' => 'spu_total',
                'label_fr' => 'Score de performance unique (SPU)',
                'category' => self::CATEGORY_SPU,
                'expression' => 'spu_cp + spu_physique + spu_delais + spu_conformite',
                'unit' => 'points',
                'precision' => 0,
                'clamp_min' => 0,
                'clamp_max' => 100,
            ],

            // ---------------------------------------------------------------
            // Lots — fiche de suivi totals (img907 to img911)
            // ---------------------------------------------------------------
            [
                'key' => 'reste_a_engager',
                'label_fr' => 'Reste à engager (lots non engagés)',
                'category' => self::CATEGORY_LOTS,
                // Lots réceptionnés and en cours de réalisation already have a
                // marché, so only unengaged lot buckets count here.
                'expression' => 'total_1 + total_2',
                'unit' => 'da',
                'precision' => 2,
                'clamp_min' => 0,
            ],

            // ---------------------------------------------------------------
            // Foncier — the fiche records "11 h 94 ar 42 ca" (img903)
            // ---------------------------------------------------------------
            [
                'key' => 'superficie_m2',
                'label_fr' => 'Superficie totale (m²)',
                'category' => self::CATEGORY_FONCIER,
                'expression' => 'superficie_ha * 10000 + superficie_a * 100 + superficie_ca',
                'unit' => 'number',
                'precision' => 2,
                'clamp_min' => 0,
            ],
        ];
    }

    /** @return list<string> */
    public static function ruleKeys(): array
    {
        return array_column(self::rules(), 'key');
    }

    /** @return list<string> */
    public static function parameterKeys(): array
    {
        return array_column(self::parameters(), 'key');
    }

    /** Human labels for each category, for grouping in the admin UI. */
    public static function categoryLabel(string $category): string
    {
        return match ($category) {
            self::CATEGORY_BUDGET => __('ui.calc.categories.budget'),
            self::CATEGORY_SPU => __('ui.calc.categories.spu'),
            self::CATEGORY_PERFORMANCE => __('ui.calc.categories.performance'),
            self::CATEGORY_LOTS => __('ui.calc.categories.lots'),
            self::CATEGORY_FONCIER => __('ui.calc.categories.foncier'),
            'alerts' => __('ui.calc.categories.alerts'),
            default => ucfirst($category),
        };
    }
}
