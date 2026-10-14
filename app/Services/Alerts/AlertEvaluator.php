<?php

namespace App\Services\Alerts;

use App\Models\Project;
use App\Services\Budget\ProjectBudget;
use App\Services\Calculation\MetricRegistry;

/**
 * Turns a project's current metrics into the alerts of TDR §7.2.
 *
 * Every trigger reads its threshold from `calculation_parameters`, so the
 * ministry can retune "alerte rouge" without a deploy.
 */
class AlertEvaluator
{
    public function __construct(private readonly MetricRegistry $registry) {}

    /**
     * Alerts that currently hold, keyed by a stable signature so a repeated
     * recompute updates rather than duplicates them.
     *
     * @return array<string, array{type: string, title: string, body: string}>
     */
    public function evaluate(Project $project, ProjectBudget $budget): array
    {
        $p = $this->registry->parameters();
        $alerts = [];

        $physique = $budget->get('physique_pct');
        $financier = $budget->get('taux_financier');
        $joursRestants = $budget->get('jours_restants');
        $tauxReevaluation = $budget->get('taux_reevaluation');
        $joursSansSituation = $budget->get('jours_depuis_situation');

        // Prévision = share of the contractual delay already elapsed.
        $contractuel = $budget->get('delai_contractuel_jours');
        $ecoules = $budget->get('jours_ecoules');
        $prevision = $contractuel > 0 ? min(100, $ecoules / $contractuel * 100) : 0.0;

        $seuilPhysique = (float) ($p['seuil_physique_vs_prevision'] ?? 50);
        if ($prevision > 0 && $physique < $prevision * $seuilPhysique / 100) {
            $alerts['physique_sous_prevision'] = [
                'type' => 'critical',
                'title' => 'Avancement physique très en retard',
                'body' => sprintf(
                    'Avancement physique de %s%% contre une prévision de %s%% : sous le seuil de %s%% de la prévision.',
                    $this->pct($physique), $this->pct($prevision), $this->pct($seuilPhysique)
                ),
            ];
        }

        $seuilEcart = (float) ($p['seuil_ecart_cp_physique'] ?? 20);
        $ecart = abs($financier - $physique);
        if ($ecart > $seuilEcart) {
            $alerts['ecart_cp_physique'] = [
                'type' => 'critical',
                'title' => 'Écart consommation / réalisation',
                'body' => sprintf(
                    'Écart de %s points entre le taux financier (%s%%) et le taux physique (%s%%), au-delà du seuil de %s points.',
                    $this->pct($ecart), $this->pct($financier), $this->pct($physique), $this->pct($seuilEcart)
                ),
            ];
        }

        $seuilDelai = (float) ($p['seuil_delai_restant_jours'] ?? 30);
        if ($joursRestants > 0 && $joursRestants < $seuilDelai && $physique < 100) {
            $alerts['delai_restant'] = [
                'type' => 'warning',
                'title' => 'Délai contractuel bientôt épuisé',
                'body' => sprintf(
                    'Il reste %d jours avant l\'échéance et le projet est à %s%% de réalisation.',
                    (int) $joursRestants, $this->pct($physique)
                ),
            ];
        }

        if ($joursRestants < 0 && $physique < 100) {
            $alerts['delai_depasse'] = [
                'type' => 'critical',
                'title' => 'Délai contractuel dépassé',
                'body' => sprintf(
                    'Échéance dépassée de %d jours, réception non prononcée (%s%% réalisé).',
                    (int) abs($joursRestants), $this->pct($physique)
                ),
            ];
        }

        $seuilCp = (float) ($p['seuil_cp_epuise'] ?? 90);
        if ($financier > $seuilCp && $physique < $financier) {
            $alerts['cp_epuise'] = [
                'type' => 'warning',
                'title' => 'Crédits de paiement presque épuisés',
                'body' => sprintf(
                    'Taux financier de %s%% au-delà du seuil de %s%%, pour un avancement physique de %s%%.',
                    $this->pct($financier), $this->pct($seuilCp), $this->pct($physique)
                ),
            ];
        }

        $seuilReev = (float) ($p['seuil_reevaluation'] ?? 10);
        if ($tauxReevaluation > $seuilReev) {
            $alerts['reevaluation'] = [
                'type' => 'warning',
                'title' => 'Réévaluation significative',
                'body' => sprintf(
                    'L\'AP a été réévaluée de %s%%, au-delà du seuil de %s%%.',
                    $this->pct($tauxReevaluation), $this->pct($seuilReev)
                ),
            ];
        }

        $seuilArret = (float) ($p['seuil_chantier_arret_jours'] ?? 60);
        if ($joursSansSituation > $seuilArret && $physique < 100) {
            $alerts['chantier_sans_situation'] = [
                'type' => 'critical',
                'title' => 'Chantier probablement à l\'arrêt',
                'body' => sprintf(
                    'Aucune situation physique depuis %d jours (seuil : %d jours).',
                    (int) $joursSansSituation, (int) $seuilArret
                ),
            ];
        }

        return $alerts;
    }

    private function pct(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1, ',', ' '), '0'), ',');
    }
}
