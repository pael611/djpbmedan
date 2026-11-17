<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risiko', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidang_id')->constrained('bidang')->cascadeOnDelete();
            $table->text('sasaran');
            $table->text('kejadian_risiko');
            $table->unsignedTinyInteger('level_kemungkinan'); // 1-5
            $table->unsignedTinyInteger('level_dampak');      // 1-5
            $table->unsignedTinyInteger('besaran_risiko');    // otomatis
            $table->string('warna_risiko', 20);               // otomatis
            $table->unsignedTinyInteger('triwulan');          // 1-4
            $table->year('tahun');
            $table->timestamps();

            $table->index(['tahun', 'triwulan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risiko');
    }
};
