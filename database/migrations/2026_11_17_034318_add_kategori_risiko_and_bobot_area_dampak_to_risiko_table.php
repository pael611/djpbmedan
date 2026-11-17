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
        Schema::table('risiko', function (Blueprint $table) {
            $table->string('kategori_risiko')->nullable();
            $table->unsignedTinyInteger('bobot_area_dampak')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risiko', function (Blueprint $table) {
            $table->dropColumn(['kategori_risiko', 'bobot_area_dampak']);
        });
    }
};
