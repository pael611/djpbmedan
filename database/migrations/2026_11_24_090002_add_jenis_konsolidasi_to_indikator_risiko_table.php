<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indikator_risiko', function (Blueprint $table) {
            $table->string('jenis_konsolidasi', 30)
                ->default('tidak_ada')
                ->after('satuan');
        });
    }

    public function down(): void
    {
        Schema::table('indikator_risiko', function (Blueprint $table) {
            $table->dropColumn('jenis_konsolidasi');
        });
    }
};
