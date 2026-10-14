<?php

namespace Database\Factories;

use App\Models\Chapitre;
use App\Models\Programme;
use App\Models\ProgrammeStructure;
use App\Models\ProgrammeType;
use App\Models\Project;
use App\Models\Secteur;
use App\Models\SousSecteur;
use App\Models\Wilaya;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/** @extends Factory<Project> */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    private const NATURES = [
        'Réalisation et équipement d\'un centre psychopédagogique',
        'Réhabilitation et équipement d\'un foyer pour personnes âgées',
        'Étude, suivi et réalisation d\'une école pour jeunes aveugles',
        'Extension et équipement d\'un établissement de l\'enfance assistée',
        'Travaux d\'étanchéité et d\'aménagement d\'un centre de formation',
        'Réalisation d\'une ferme pédagogique',
        'Réhabilitation du siège de la direction de l\'action sociale',
        'Équipement d\'un centre pour handicapés moteurs',
    ];

    public function definition(): array
    {
        $wilaya = Wilaya::inRandomOrder()->first();
        $inscription = Carbon::instance(fake()->dateTimeBetween('-10 years', '-2 years'));
        $ap = fake()->numberBetween(8, 200) * 1_000_000;

        $prefix = fake()->randomElement(['NK', 'NE']);
        $chapitre = Chapitre::inRandomOrder()->first();
        $sousSecteur = SousSecteur::inRandomOrder()->first();

        return [
            'fip_id' => 'FIP-'.$inscription->year.'-'.fake()->unique()->numerify('####'),
            'operation_prefix' => $prefix,
            'operation_number' => sprintf(
                '%s 5.%s.%d.262.%s.%s.%02d',
                $prefix,
                $chapitre?->code ?? '764',
                fake()->numberBetween(1, 9),
                str_pad((string) ($wilaya?->id ?? 1), 3, '0', STR_PAD_LEFT),
                $inscription->format('y'),
                fake()->numberBetween(1, 5),
            ),
            'title_fr' => fake()->randomElement(self::NATURES).' à '.($wilaya?->name_fr ?? 'Alger'),
            'title_ar' => 'إنجاز وتجهيز مؤسسة اجتماعية بـ '.($wilaya?->name_ar ?? 'الجزائر'),
            'programme_id' => Programme::inRandomOrder()->value('id'),
            'wilaya_id' => $wilaya?->id,
            'nature' => fake()->randomElement(['new', 'extension', 'rehabilitation']),
            'status' => 'in_execution',
            'phase' => fake()->randomElement(['realisation', 'lancement', 'en_equipement', 'acheve']),
            'capacity' => fake()->randomElement([50, 60, 80, 100, 120, 200]),
            'nbr_operations' => 1,
            'secteur_id' => Secteur::inRandomOrder()->value('id'),
            'sous_secteur_id' => $sousSecteur?->id,
            'chapitre_id' => $chapitre?->id,
            'programme_type_id' => ProgrammeType::inRandomOrder()->value('id'),
            'programme_structure_id' => ProgrammeStructure::inRandomOrder()->value('id'),
            'decision_programme_ref' => 'MF/'.$inscription->year.'/DP/'.fake()->numberBetween(10, 200),
            'inscription_date' => $inscription,
            'individualisation_number' => fake()->numerify('###'),
            'individualisation_date' => $inscription->copy()->addDays(fake()->numberBetween(10, 120)),
            'notification_year' => $inscription->year,
            'titre' => fake()->randomElement(['t3', 't4']),
            'managed_by' => fake()->randomElement(['centrale', 'dass', 'dep']),
            'ae_da' => $ap,
            'cp_da' => $cpNotified = (int) round($ap * fake()->randomFloat(2, 0.4, 0.95)),

            // Columns AA–AC of the DGB canevas.
            'cp_notification_current_year_da' => $cpAnnee = (int) round($cpNotified * fake()->randomFloat(2, 0.15, 0.4)),
            'cp_reliquat_da' => (int) round($cpAnnee * fake()->randomFloat(2, 0, 0.5)),
            'cp_besoins_da' => (int) round($cpAnnee * fake()->randomFloat(2, 0.5, 1.6)),

            'ods_date' => $inscription->copy()->addMonths(fake()->numberBetween(6, 30)),
            'project_duration_months' => fake()->numberBetween(12, 48),
            'deadline' => $inscription->copy()->addMonths(fake()->numberBetween(24, 72)),
            'fiche_updated_at' => now(),
            'budget_type' => 'T3',
        ];
    }

    /**
     * An operation running to plan: started recently, deadline still ahead.
     * Without this the whole demo portfolio reads red, because a randomly
     * dated past deadline trips the "délai dépassé" rule every time.
     */
    public function onTrack(): static
    {
        $ods = Carbon::instance(fake()->dateTimeBetween('-18 months', '-6 months'));

        return $this->state([
            'inscription_date' => $ods->copy()->subMonths(8),
            'ods_date' => $ods,
            'initial_launch_date' => $ods,
            'project_duration_months' => 36,
            'deadline' => $ods->copy()->addMonths(36),
            'phase' => 'realisation',
            'status' => 'in_execution',
        ]);
    }

    public function closed(): static
    {
        return $this->state([
            'status' => 'closed',
            'phase' => 'acheve_equipe',
            'is_closed' => true,
            'observations' => 'Opér. clôturée en '.now()->subYear()->year,
            'reception_provisoire_date' => now()->subYears(2),
            'reception_definitive_date' => now()->subYear(),
        ]);
    }

    public function inWilaya(int $wilayaId): static
    {
        return $this->state(['wilaya_id' => $wilayaId]);
    }
}
