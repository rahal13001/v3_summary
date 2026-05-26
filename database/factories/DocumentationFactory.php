<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Documentation>
 */
class DocumentationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_id' => $this->faker->numberBetween(1311, 10000),
            'dokumentasi1' => $this->faker->sentence(),
            'dokumentasi2' => $this->faker->sentence(),
            'dokumentasi3' => $this->faker->sentence(),
            'st' => $this->faker->sentence(),
            'lainnya' => $this->faker->sentence(),
        ];
    }
}
