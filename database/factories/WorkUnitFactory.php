<?php

namespace Database\Factories;

use App\Models\WorkUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WorkUnit> */
class WorkUnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'status' => WorkUnit::STATUS_ACTIVE,
            'unit' => fake()->randomElement(array_keys(WorkUnit::unitOptions())),
        ];
    }
}
