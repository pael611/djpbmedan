<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_awal_risiko', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun');
            $table->string('kejadian_risiko', 255);
            $table->unsignedTinyInteger('nilai_awal');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tahun', 'kejadian_risiko']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_awal_risiko');
    }
};
