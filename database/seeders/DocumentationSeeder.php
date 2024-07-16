<?php

namespace Database\Seeders;

use App\Models\Documentation;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DocumentationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Documentation::factory()
            ->count(10000)
            ->create();
    }
}
