<?php

namespace Database\Factories;

use App\Models\AeEngagement;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AeEngagement> */
class AeEngagementFactory extends Factory
{
    protected $model = AeEngagement::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'amount_da' => fake()->numberBetween(1_000, 40_000) * 1_000,
            'description' => 'Engagement marché lot n°'.fake()->numberBetween(1, 12),
            'engaged_at' => fake()->dateTimeBetween('-4 years', 'now'),
            'created_by' => User::where('role', 'admin')->value('id'),
        ];
    }
}
