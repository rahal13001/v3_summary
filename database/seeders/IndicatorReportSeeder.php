<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\Indicator;
use App\Models\IndicatorReport;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class IndicatorReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Assuming you already have indicators and reports in your database
        // and you're linking them randomly for demonstration purposes

        $indicatorIds = Indicator::pluck('id'); // Get all indicator IDs
        $reportIds = Report::where('id','>', 1311)
                            ->where('id','<=', 11294)                    
                            ->pluck('id'); // Get all report IDs

                            foreach ($reportIds as $reportId) {
                                // Determine the maximum number of items you can request
                                $maxItems = min($indicatorIds->count(), rand(55, 77));
                            
                                // Get a random number of indicator IDs, ensuring not to exceed the collection size
                                $randomIndicatorIds = $indicatorIds->random($maxItems)->all();
                            
                                $report = Report::find($reportId); // Find the report by ID
                            
                                // Attach the random indicators to the report
                                $report->indicators()->attach($randomIndicatorIds);
                            }
    }
}
