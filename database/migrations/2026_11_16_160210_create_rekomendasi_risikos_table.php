<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekomendasi_risiko', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risiko_id')->constrained('risiko')->cascadeOnDelete();
            $table->unsignedTinyInteger('triwulan');
            $table->year('tahun');
            $table->longText('deskripsi_rekomendasi');
            $table->unsignedTinyInteger('rekomendasi_level_kemungkinan')->nullable();
            $table->unsignedTinyInteger('rekomendasi_level_dampak')->nullable();
            $table->unsignedTinyInteger('rekomendasi_besaran')->nullable();
            $table->timestamps();

            $table->unique(['risiko_id', 'tahun', 'triwulan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_risiko');
    }
};
