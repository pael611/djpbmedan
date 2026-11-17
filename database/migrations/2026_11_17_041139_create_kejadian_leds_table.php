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
        Schema::create('kejadian_led', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risiko_id')->constrained('risiko')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('terjadi_pada');
            $table->text('uraian_kejadian');
            $table->text('dampak');
            $table->text('tindakan_mitigasi')->nullable();
            $table->text('kondisi_setelah_mitigasi')->nullable();
            $table->boolean('kejadian_luar_biasa')->default(false);
            $table->dateTime('dilaporkan_pada')->nullable();
            $table->timestamps();

            $table->index(['risiko_id', 'terjadi_pada']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kejadian_led');
    }
};
