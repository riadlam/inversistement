<?php

namespace Database\Factories;

use App\Models\PhysicalProgress;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PhysicalProgress> */
class PhysicalProgressFactory extends Factory
{
    protected $model = PhysicalProgress::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => 'Situation physique '.fake()->monthName().' '.fake()->year(),
            'progress_pct' => fake()->numberBetween(5, 100),
            'status' => 'accepted',
            'reported_by' => User::where('role', 'dass')->value('id'),
            'accepted_by' => User::where('role', 'admin')->value('id'),
            'reported_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
