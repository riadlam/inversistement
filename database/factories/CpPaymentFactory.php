<?php

namespace Database\Factories;

use App\Models\CpPayment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CpPayment> */
class CpPaymentFactory extends Factory
{
    protected $model = CpPayment::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'amount_da' => fake()->numberBetween(500, 20_000) * 1_000,
            'payment_type' => 'regular',
            'description' => 'Situation n°'.fake()->numberBetween(1, 12),
            'status' => 'validated',
            'mandate_number' => fake()->numerify('MD-####/##'),
            'paid_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'created_by' => User::where('role', 'dass')->value('id'),
        ];
    }

    public function pending(): static
    {
        return $this->state([
            'status' => 'pending',
            'mandate_number' => null,
            'paid_at' => null,
        ]);
    }
}
