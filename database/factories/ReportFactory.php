<?php

namespace Database\Factories;

use App\Models\Documentation;
use App\Models\IndicatorReport;
use App\Models\ReportTeam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 40),
            'slug' => $this->faker->slug(),
            'no_st' => $this->faker->word(),
            'what' => $this->faker->sentence(),
            'why' => $this->faker->sentence(),
            'when' => $this->faker->date(),
            'tanggal_selesai' => $this->faker->date(),
            'where' => $this->faker->sentence(),
            'who' => $this->faker->sentence(),
            'how' => $this->faker->sentence(),
            'penyelenggara' => $this->faker->sentence(),
            'total_peserta' => $this->faker->numberBetween(1, 100),
            'total_wanita' => $this->faker->numberBetween(1, 100),
            'kode' => $this->faker->word(),


        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (\App\Models\Report $report) {
            // Assuming you want to create between 1 to 5 comments for each report
            Documentation::factory()->create([
                'report_id' => $report->id, // Set the foreign key to link the comment to the report
                // Other fields for the Comment model can be filled with Faker data inside the CommentFactory
                'dokumentasi1' => $this->faker->sentence(),
                'dokumentasi2' => $this->faker->sentence(),
                'dokumentasi3' => $this->faker->sentence(),
                'st' => $this->faker->sentence(),
                'lainnya' => $this->faker->sentence(),
            ]);

            // // You can also create other related models here
            // ReportTeam::factory()->create([
            //     'report_id' => $report->id,
            //     'team_id' => $this->faker->numberBetween(1, 4),
            //     // Other fields for the Team model can be filled with Faker data inside the TeamFactory
            // ]);

            // // You can also create other related models here
            // IndicatorReport::factory()->create([
            //     'report_id' => $report->id,
            //     'indicator_id' => $this->faker->numberBetween(55, 77),
            //     // Other fields for the Indicator model can be filled with Faker data inside the IndicatorFactory
            // ]);


        });
    }
}
