<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_settings', function (Blueprint $table): void {
            $table->string('app_name')->nullable()->after('key');
            $table->string('favicon_path')->nullable()->after('logo_path');
            $table->string('organizer_name')->nullable()->after('address');
            $table->string('organizer_input_mode')->default('locked')->after('organizer_name');
        });
    }

    public function down(): void
    {
        Schema::table('organization_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'app_name',
                'favicon_path',
                'organizer_name',
                'organizer_input_mode',
            ]);
        });
    }
};
