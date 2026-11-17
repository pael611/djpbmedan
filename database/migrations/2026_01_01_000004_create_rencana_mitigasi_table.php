<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rencana_mitigasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risiko_id')->constrained('risiko')->cascadeOnDelete();
            $table->text('deskripsi_rencana');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rencana_mitigasi');
    }
};
