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
        Schema::table('kejadian_led', function (Blueprint $table) {
            $table->string('bukti_path')->nullable();
            $table->string('status_verifikasi', 20)->default('approved');
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->text('catatan_verifikasi')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kejadian_led', function (Blueprint $table) {
            $table->dropConstrainedForeignId('diverifikasi_oleh');
            $table->dropColumn([
                'bukti_path',
                'status_verifikasi',
                'diverifikasi_pada',
                'catatan_verifikasi',
            ]);
        });
    }
};
