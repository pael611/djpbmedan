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
        Schema::create('pemantauan_iru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_risiko_id')
                ->constrained('indikator_risiko')
                ->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('tanggal_pengukuran');
            $table->decimal('nilai_aktual', 18, 4);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['indikator_risiko_id', 'tanggal_pengukuran']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemantauan_iru');
    }
};
