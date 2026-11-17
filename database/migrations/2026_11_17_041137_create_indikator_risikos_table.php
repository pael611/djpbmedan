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
        Schema::create('indikator_risiko', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risiko_id')->constrained('risiko')->cascadeOnDelete();
            $table->string('nama_indikator', 150);
            $table->text('definisi')->nullable();
            $table->string('satuan', 50);
            $table->text('formula')->nullable();
            $table->string('polarisasi', 20);
            $table->decimal('batas_aman', 18, 4)->nullable();
            $table->decimal('batas_aman_bawah', 18, 4)->nullable();
            $table->decimal('batas_aman_atas', 18, 4)->nullable();
            $table->decimal('batas_atas', 18, 4)->nullable();
            $table->decimal('batas_bawah', 18, 4)->nullable();
            $table->string('periode_pemantauan', 20);
            $table->string('penanggung_jawab', 150)->nullable();
            $table->string('penyedia_data', 150)->nullable();
            $table->text('sumber_data')->nullable();
            $table->timestamps();

            $table->unique(['risiko_id', 'nama_indikator']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indikator_risiko');
    }
};
