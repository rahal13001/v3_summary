<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\ReportEvaluation;
use App\Models\User;
use App\Models\WorkUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ReportEvaluation> */
class ReportEvaluationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'report_id' => Report::factory()->for(User::factory()),
            'work_unit_id' => WorkUnit::factory(),
            'period' => today()->startOfMonth(),
            'rencana_pelaksanaan' => fake()->optional()->paragraph(),
            'kendala' => fake()->optional()->paragraph(),
            'saran_rekomendasi' => fake()->optional()->paragraph(),
            'tindak_lanjut' => fake()->optional()->paragraph(),
            'evidence_links' => [],
            'keterangan' => fake()->optional()->paragraph(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (ReportEvaluation $evaluation): void {
            $evaluation->report->workUnits()->syncWithoutDetaching($evaluation->work_unit_id);
        });
    }
}
