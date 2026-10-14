<?php

namespace Database\Factories;

use App\Models\LotMilestone;
use App\Models\ProjectLot;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LotMilestone> */
class LotMilestoneFactory extends Factory
{
    protected $model = LotMilestone::class;

    public function definition(): array
    {
        return [
            'lot_id' => ProjectLot::factory(),
            'type' => fake()->randomElement(array_keys(LotMilestone::typeLabels())),
            'date' => fake()->dateTimeBetween('-5 years', 'now'),
            'reference' => fake()->numerify('N° ###/##'),
        ];
    }

    public function ofType(string $type, ?string $date = null, ?string $reference = null): static
    {
        return $this->state(array_filter([
            'type' => $type,
            'date' => $date,
            'reference' => $reference,
        ]));
    }
}
