<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IndicatorReport>
 */
class IndicatorReportFactory extends Factory
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
            'indicator_id' => $this->faker->numberBetween(55, 77),
        ];
    }
}
