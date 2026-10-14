<?php

namespace Database\Factories;

use App\Models\Contractor;
use App\Models\Project;
use App\Models\ProjectLot;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProjectLot> */
class ProjectLotFactory extends Factory
{
    protected $model = ProjectLot::class;

    private const DESIGNATIONS = [
        'Terrassement et gros œuvre',
        'Charpente et couverture',
        'Menuiserie bois et aluminium',
        'Étanchéité',
        'Électricité et courants faibles',
        'Plomberie sanitaire',
        'Peinture et revêtements',
        'Voirie et réseaux divers',
        'Clôture et portails',
        'Chauffage et climatisation',
        'Espaces verts et aménagement extérieur',
        'Équipement et mobilier',
    ];

    public function definition(): array
    {
        $amount = fake()->numberBetween(2, 40) * 1_000_000;

        return [
            'project_id' => Project::factory(),
            'tranche' => 1,
            'lot_number' => fake()->numberBetween(1, 12),
            'designation' => fake()->randomElement(self::DESIGNATIONS),
            'kind' => 'travaux',
            'state' => ProjectLot::STATE_NON_LANCE,
            'amount_da' => 0,
            'estimation_da' => $amount,
            'estimated_by' => 'DASS',
            'estimation_basis' => fake()->randomElement(['bet', 'consultation']),
            'delai_months' => fake()->numberBetween(3, 18),
            'progress_pct' => 0,
        ];
    }

    public function receptionne(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => ProjectLot::STATE_RECEPTIONNE,
            'amount_da' => $attributes['estimation_da'],
            'contractor_id' => Contractor::factory(),
            'launch_date' => fake()->dateTimeBetween('-6 years', '-3 years'),
            'reception_date' => fake()->dateTimeBetween('-3 years', '-1 year'),
            'progress_pct' => 100,
        ]);
    }

    public function enCoursRealisation(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => ProjectLot::STATE_EN_COURS_REALISATION,
            'amount_da' => $attributes['estimation_da'],
            'contractor_id' => Contractor::factory(),
            'launch_date' => fake()->dateTimeBetween('-2 years', '-6 months'),
            'progress_pct' => fake()->numberBetween(10, 90),
        ]);
    }

    public function enCoursLancement(): static
    {
        return $this->state([
            'state' => ProjectLot::STATE_EN_COURS_LANCEMENT,
            'amount_da' => 0,
            'blocking_reason' => fake()->randomElement([
                'attente_visa_controle_financier',
                'attribution_provisoire',
            ]),
        ]);
    }

    public function nonLance(): static
    {
        return $this->state([
            'state' => ProjectLot::STATE_NON_LANCE,
            'amount_da' => 0,
        ]);
    }
}
