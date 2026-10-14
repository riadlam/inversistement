<?php

namespace Database\Factories;

use App\Models\Contractor;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Contractor> */
class ContractorFactory extends Factory
{
    protected $model = Contractor::class;

    public function definition(): array
    {
        $form = fake()->randomElement(['etb', 'sarl', 'eeg', 'eurl', 'spa']);

        return [
            'name' => strtoupper($form).' '.fake()->lastName().' '.fake()->firstName(),
            'legal_form' => $form,
            // Algerian NIF: 15 digits.
            'nif' => (string) fake()->numerify('###############'),
            'business_register' => fake()->numerify('##/##-######?##'),
            'address' => fake()->streetAddress(),
            'phone' => fake()->numerify('0#########'),
        ];
    }
}
