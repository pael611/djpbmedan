<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progres_mitigasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rencana_id')->constrained('rencana_mitigasi')->cascadeOnDelete();
            $table->text('deskripsi_progres');
            $table->unsignedTinyInteger('rekomendasi_level_kemungkinan')->nullable(); // 1-5
            $table->unsignedTinyInteger('rekomendasi_level_dampak')->nullable();      // 1-5
            $table->unsignedTinyInteger('rekomendasi_besaran')->nullable();           // otomatis
            $table->unsignedTinyInteger('bulan');                                     // 1-12
            $table->year('tahun');
            $table->string('file_bukti')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progres_mitigasi');
    }
};
