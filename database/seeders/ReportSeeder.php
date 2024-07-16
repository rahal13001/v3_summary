<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Report;
use App\Models\Indicator;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reports =  Report::factory()
            ->count(100000)
            ->create();

            $reports->each(function ($report) {
                // Assuming you have defined relationships in your Report model
                // Attach teams to each report
                $teamIds = Team::inRandomOrder()->take(rand(1, 4))->pluck('id');
                $report->teams()->attach($teamIds);
    
                // Attach indicators to each report
                $indicatorIds = Indicator::inRandomOrder()->take(rand(55, 77))->pluck('id');
                $report->indicators()->attach($indicatorIds);
            });
    }
}
