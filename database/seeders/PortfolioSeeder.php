<?php

namespace Database\Seeders;

use App\Models\AeEngagement;
use App\Models\Contractor;
use App\Models\CpPayment;
use App\Models\MaturationChecklistDefinition;
use App\Models\PhysicalProgress;
use App\Models\Project;
use App\Models\ProjectLot;
use App\Models\ProjectNetworkLink;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * A portfolio wide enough to exercise every dashboard.
 *
 * Projects are seeded in three cohorts rather than at random, because the SPU
 * of TDR §7.3 scores *absolute* completion: a young operation scores low
 * however well it is run, so a green traffic light only exists on a project
 * near delivery. Seeding by cohort is the only way to populate all three
 * bands and, with them, every alert rule.
 */
class PortfolioSeeder extends Seeder
{
    private const COHORTS = [
        // profile => how many
        'nearing_completion' => 8,
        'on_track' => 8,
        'troubled' => 8,
    ];

    public function run(): void
    {
        $existing = Project::where('fip_id', 'like', 'FIP-%')
            ->where('fip_id', '!=', 'FIP-2010-BISKRA-FP')
            ->count();

        if ($existing >= array_sum(self::COHORTS)) {
            $this->command?->info('Portfolio already seeded, skipping.');

            return;
        }

        $author = User::where('role', 'admin')->first();
        $contractors = Contractor::factory()->count(15)->create();
        $checklist = MaturationChecklistDefinition::all();

        foreach (self::COHORTS as $profile => $count) {
            $factory = Project::factory()->count($count);

            if ($profile !== 'troubled') {
                $factory = $factory->onTrack();
            }

            $factory->create(['created_by' => $author?->id])
                ->each(function (Project $project) use ($profile, $contractors, $checklist) {
                    $this->addReevaluations($project, $profile);
                    $this->addLots($project, $profile, $contractors);
                    $this->addExecution($project, $profile);
                    $this->addNetworks($project, $profile);
                    $this->addMaturation($project, $profile, $checklist);
                });
        }

        // Clôturées, so the DGB export has CLOTUREE rows to print.
        Project::factory()->count(4)->closed()->create(['created_by' => $author?->id]);
    }

    private function addReevaluations(Project $project, string $profile): void
    {
        $count = match ($profile) {
            'troubled' => fake()->numberBetween(1, 3),
            'nearing_completion' => fake()->randomElement([0, 1]),
            default => fake()->randomElement([0, 0, 1]),
        };

        for ($sequence = 1; $sequence <= $count; $sequence++) {
            \App\Models\AeRevaluation::factory()->create([
                'project_id' => $project->id,
                'sequence' => $sequence,
                'requested_amount_da' => (int) round($project->ae_da * fake()->randomFloat(2, 0.05, 0.3)),
            ]);
        }

        $project->update(['reevaluation_count' => $count]);
    }

    /**
     * How the lots are distributed across the four fiche states, per cohort.
     *
     * @return list<string>
     */
    private function lotStates(string $profile, int $count): array
    {
        $mix = match ($profile) {
            'nearing_completion' => [
                ProjectLot::STATE_RECEPTIONNE => 0.75,
                ProjectLot::STATE_EN_COURS_REALISATION => 0.25,
            ],
            'on_track' => [
                ProjectLot::STATE_RECEPTIONNE => 0.3,
                ProjectLot::STATE_EN_COURS_REALISATION => 0.4,
                ProjectLot::STATE_EN_COURS_LANCEMENT => 0.3,
            ],
            default => [
                ProjectLot::STATE_RECEPTIONNE => 0.15,
                ProjectLot::STATE_EN_COURS_REALISATION => 0.2,
                ProjectLot::STATE_EN_COURS_LANCEMENT => 0.35,
                ProjectLot::STATE_NON_LANCE => 0.3,
            ],
        };

        $states = [];
        foreach ($mix as $state => $share) {
            $states = array_merge($states, array_fill(0, max(1, (int) round($count * $share)), $state));
        }

        return array_slice(array_pad($states, $count, array_key_first($mix)), 0, $count);
    }

    private function addLots(Project $project, string $profile, Collection $contractors): void
    {
        $apActuelle = (float) $project->ae_da
            + (float) $project->aeRevaluations()->where('status', 'approved')->sum('requested_amount_da');

        $lotCount = fake()->numberBetween(5, 10);
        $tranches = $lotCount > 7 ? 2 : 1;
        $perLot = (int) floor($apActuelle * 0.92 / $lotCount);
        $states = $this->lotStates($profile, $lotCount);

        $index = 0;
        for ($tranche = 1; $tranche <= $tranches; $tranche++) {
            $inTranche = (int) ceil($lotCount / $tranches);

            for ($number = 1; $number <= $inTranche && $index < $lotCount; $number++, $index++) {
                $state = $states[$index];
                $awarded = in_array($state, [ProjectLot::STATE_RECEPTIONNE, ProjectLot::STATE_EN_COURS_REALISATION], true);

                ProjectLot::factory()->create([
                    'project_id' => $project->id,
                    'tranche' => $tranche,
                    'lot_number' => $number,
                    'estimation_da' => $perLot,
                    'state' => $state,
                    'amount_da' => $awarded ? $perLot : 0,
                    'contractor_id' => $awarded ? $contractors->random()->id : null,
                    'progress_pct' => match ($state) {
                        ProjectLot::STATE_RECEPTIONNE => 100,
                        ProjectLot::STATE_EN_COURS_REALISATION => fake()->numberBetween(30, 95),
                        default => 0,
                    },
                    'launch_date' => $awarded ? $project->ods_date : null,
                    'reception_date' => $state === ProjectLot::STATE_RECEPTIONNE
                        ? fake()->dateTimeBetween('-3 years', '-2 months')
                        : null,
                    'blocking_reason' => $state === ProjectLot::STATE_EN_COURS_LANCEMENT
                        ? fake()->randomElement(['attente_visa_controle_financier', 'infructueux_offres_excessives', 'visa_refuse_plafond'])
                        : null,
                ]);
            }
        }
    }

    private function addExecution(Project $project, string $profile): void
    {
        $author = User::where('role', 'admin')->value('id');
        $engaged = (float) $project->lots()->sum('amount_da');

        if ($engaged > 0) {
            AeEngagement::factory()->create([
                'project_id' => $project->id,
                'amount_da' => $engaged,
                'description' => 'Cumul des marchés notifiés',
                'engaged_at' => $project->ods_date ?? now()->subYear(),
                'created_by' => $author,
            ]);
        }

        $apActuelle = (float) $project->ae_da
            + (float) $project->aeRevaluations()->where('status', 'approved')->sum('requested_amount_da');

        // Share of the AP already paid, which is what the SPU's CP component
        // and the traffic light both key off.
        $target = $apActuelle * match ($profile) {
            'nearing_completion' => fake()->randomFloat(2, 0.80, 0.95),
            'on_track' => fake()->randomFloat(2, 0.40, 0.65),
            default => fake()->randomFloat(2, 0.05, 0.30),
        };

        $target = min($target, $engaged > 0 ? $engaged : $target);
        $installments = fake()->numberBetween(3, 7);

        for ($i = 1; $i <= $installments; $i++) {
            CpPayment::factory()->create([
                'project_id' => $project->id,
                'amount_da' => (int) round($target / $installments),
                'description' => "Situation n°{$i}",
                'created_by' => $author,
            ]);
        }

        CpPayment::factory()->pending()->create([
            'project_id' => $project->id,
            'amount_da' => (int) round($apActuelle * 0.04),
            'created_by' => $author,
        ]);

        // Physical rate tracks the financial one, within the écart threshold
        // for healthy cohorts and outside it for the troubled one.
        $financier = $apActuelle > 0 ? $target / $apActuelle * 100 : 0;
        $spread = $profile === 'troubled' ? fake()->numberBetween(-45, -20) : fake()->numberBetween(-12, 12);

        PhysicalProgress::factory()->create([
            'project_id' => $project->id,
            'progress_pct' => (int) round(max(0, min(100, $financier + $spread))),
            'reported_at' => now()->subDays($profile === 'troubled'
                ? fake()->numberBetween(70, 200)
                : fake()->numberBetween(3, 40)),
            'reported_by' => $author,
            'accepted_by' => $author,
        ]);
    }

    private function addNetworks(Project $project, string $profile): void
    {
        $connected = $profile === 'nearing_completion' ? 90 : ($profile === 'on_track' ? 50 : 15);

        foreach (ProjectNetworkLink::networkCodes() as $network) {
            ProjectNetworkLink::create([
                'project_id' => $project->id,
                'network' => $network,
                'connected' => fake()->boolean($connected),
            ]);
        }
    }

    private function addMaturation(Project $project, string $profile, Collection $checklist): void
    {
        if ($checklist->isEmpty()) {
            return;
        }

        // "Dossier complet" is all-or-nothing in the SPU, so a partial tick
        // scores the same as none; the cohorts differ on whether it is closed.
        $complete = match ($profile) {
            'nearing_completion' => fake()->boolean(90),
            'on_track' => fake()->boolean(60),
            default => fake()->boolean(15),
        };

        $project->maturationItems()->sync(
            $checklist->mapWithKeys(fn ($item) => [
                $item->id => ['checked' => $complete || fake()->boolean(50)],
            ])->all()
        );
    }
}
