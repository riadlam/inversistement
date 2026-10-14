<?php

namespace Database\Factories;

use App\Models\AeRevaluation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AeRevaluation> */
class AeRevaluationFactory extends Factory
{
    protected $model = AeRevaluation::class;

    public function definition(): array
    {
        $year = fake()->numberBetween(2013, (int) date('Y'));

        return [
            'project_id' => Project::factory(),
            'sequence' => 1,
            'year' => $year,
            'decision_ref' => 'MF/'.$year.'/DP/'.fake()->numberBetween(10, 200),
            'requested_amount_da' => fake()->numberBetween(5, 60) * 1_000_000,
            'justification' => 'Réévaluation pour prise en charge des travaux supplémentaires.',
            'status' => 'approved',
            'submitted_by' => User::where('role', 'dass')->value('id'),
            'approved_by' => User::where('role', 'admin')->value('id'),
            'approved_at' => fake()->dateTimeBetween('-5 years', 'now'),
        ];
    }
}
