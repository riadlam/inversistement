<?php

namespace App\Exports;

use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Short teleprompter PDF for live demos to project owners.
 */
class PitchTeleprompterPdf
{
    public const DEMO_FIP = 'FIP-2010-BISKRA-FP';

    public function contents(): string
    {
        return Pdf::loadView('exports.pitch-teleprompter', $this->data())
            ->setPaper('a4', 'portrait')
            ->output();
    }

    public function filename(): string
    {
        return 'pitch-teleprompter.pdf';
    }

    /**
     * @return array{baseUrl: string, demoFip: string, steps: list<array{n: string, title: string, say: string, url: string}>, hidden: list<array{title: string, url: string}>, accounts: list<array{email: string, role: string, scope: string}>}
     */
    public function data(): array
    {
        $base = rtrim((string) config('app.url'), '/');
        $fip = self::DEMO_FIP;
        $dossier = "{$base}/dashbored/projects/{$fip}";
        $fiche = "{$dossier}/fiche";

        return [
            'baseUrl' => $base,
            'demoFip' => $fip,
            'steps' => [
                [
                    'n' => '0',
                    'title' => 'Sign in',
                    'say' => 'Open the app and sign in with a demo email. Password is always password.',
                    'url' => "{$base}/register",
                ],
                [
                    'n' => '1',
                    'title' => 'What it is',
                    'say' => 'Public investment follow-up: one dossier, one number engine, from wilaya DASS up to the ministry.',
                    'url' => "{$base}/dashbored",
                ],
                [
                    'n' => '2',
                    'title' => 'Who uses it',
                    'say' => 'Switch roles as you demo: DASS (field) → Responsable de programme → Contrôleur budgétaire → Ordonnateur.',
                    'url' => '— (accounts on last page)',
                ],
                [
                    'n' => '3',
                    'title' => 'Create & mature',
                    'say' => 'As DASS: open a draft, complete the maturation checklist, submit to arbitration.',
                    'url' => "{$base}/dashbored/projects/create  ·  {$base}/dashbored/projects",
                ],
                [
                    'n' => '4',
                    'title' => 'Arbitration',
                    'say' => 'As Programme Manager: open the dossier. Accept or return with a reason. Requester shows as DASS · Biskra.',
                    'url' => "{$dossier}  (top banner)",
                ],
                [
                    'n' => '5',
                    'title' => 'Visa',
                    'say' => 'As Budget Controller: grant the compliance visa or decline with a motif.',
                    'url' => "{$dossier}  (visa banner)",
                ],
                [
                    'n' => '6',
                    'title' => 'Engage AE',
                    'say' => 'As Ordonnateur: record the AE engagement. Status moves into execution.',
                    'url' => "{$dossier}  (AE form)",
                ],
                [
                    'n' => '7',
                    'title' => 'Fiche field work',
                    'say' => 'As DASS: walk the fiche tabs in order — lots, land, networks, project situation, identification, physical situations.',
                    'url' => "{$fiche}  ·  ?tab=reseaux  ·  ?tab=situation  ·  ?tab=identification  ·  ?tab=physique",
                ],
                [
                    'n' => '8',
                    'title' => 'CP request',
                    'say' => 'As DASS: submit a CP. As BC/Ordonnateur: validate or reject. Banner shows who asked (DASS · Biskra).',
                    'url' => "{$dossier}/cp  ·  also {$dossier}",
                ],
                [
                    'n' => '9',
                    'title' => 'Accept situations',
                    'say' => 'As Programme Manager: open the dossier — pending physical situations appear first in an amber banner.',
                    'url' => $dossier,
                ],
                [
                    'n' => '10',
                    'title' => 'Actions mid-life',
                    'say' => 'Réévaluation, mouvement, clôture — Fiches 04 to 06.',
                    'url' => "{$base}/dashbored/actions",
                ],
                [
                    'n' => '11',
                    'title' => 'Exports',
                    'say' => 'Same numbers leave the system as the official fiche PDF and the DGB état global Excel.',
                    'url' => "{$dossier}/fiche-suivi.pdf  ·  {$base}/dashbored/reports/etat-global.xlsx  ·  {$base}/dashbored/reports",
                ],
            ],
            'hidden' => [
                ['title' => 'One formula engine for dashboard, PDF, and Excel', 'url' => "{$base}/dashbored/moteur-de-calcul"],
                ['title' => 'SPU performance score + traffic lights', 'url' => "{$base}/dashbored/spu"],
                ['title' => 'Alerts (live refresh when Reverb is running)', 'url' => "{$base}/dashbored/alerts"],
                ['title' => 'Financial / physical boards', 'url' => "{$base}/dashbored/financial  ·  {$base}/dashbored/physical"],
                ['title' => 'Lot % ≠ official physical % — situations must be accepted', 'url' => "{$fiche}?tab=physique  ·  {$dossier}"],
                ['title' => 'PDF phase checkbox is set on fiche (Situation du projet)', 'url' => "{$fiche}?tab=situation"],
                ['title' => 'Wilaya scope — DASS sees only Biskra; RP sees national', 'url' => "{$base}/dashbored/projects"],
            ],
            'accounts' => [
                ['email' => 'dass_demo@msnfcf.gov.dz', 'role' => 'DASS', 'scope' => 'Biskra'],
                ['email' => 'programme_manager@msnfcf.gov.dz', 'role' => 'RP', 'scope' => 'National'],
                ['email' => 'budget_controller@msnfcf.gov.dz', 'role' => 'CB', 'scope' => 'National'],
                ['email' => 'ordonnateur@msnfcf.gov.dz', 'role' => 'Ordonnateur', 'scope' => 'National'],
                ['email' => 'admin@msnfcf.gov.dz', 'role' => 'Admin', 'scope' => 'National'],
            ],
        ];
    }
}
