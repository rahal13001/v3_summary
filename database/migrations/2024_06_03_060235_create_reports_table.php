<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('slug')->unique();
            $table->string('no_st');
            $table->text('what');
            $table->text('why');
            $table->date('when');
            $table->date('tanggal_selesai');
            $table->text('where');
            $table->text('who');
            $table->text('how');
            $table->string('penyelenggara');
            $table->string('total_peserta');
            $table->string('total_wanita');
            $table->string('kode');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
