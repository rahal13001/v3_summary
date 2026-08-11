<?php

namespace Database\Seeders;

use App\Models\Involvement;
use Illuminate\Database\Seeder;

class InvolvementSeeder extends Seeder
{
    public function run(): void
    {
        Involvement::query()->firstOrCreate(
            ['name' => 'Penyelenggara'],
            [
                'status' => Involvement::STATUS_ACTIVE,
                'is_lprl_organizer' => true,
            ],
        );

        Involvement::query()->firstOrCreate(
            ['name' => 'Peserta'],
            [
                'status' => Involvement::STATUS_ACTIVE,
                'is_lprl_organizer' => false,
            ],
        );
    }
}
