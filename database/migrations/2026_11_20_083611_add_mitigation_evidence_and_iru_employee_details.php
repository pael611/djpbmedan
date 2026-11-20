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
        Schema::table('rencana_mitigasi', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('bukti_path')->nullable();
            $table->string('status_verifikasi', 20)->default('approved');
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->text('catatan_verifikasi')->nullable();
        });

        Schema::table('progres_mitigasi', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('bukti_path')->nullable();
            $table->string('status_verifikasi', 20)->default('approved');
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->text('catatan_verifikasi')->nullable();
        });

        Schema::table('pemantauan_iru', function (Blueprint $table) {
            $table->string('jenis_pegawai_perbaikan', 150)->nullable();
            $table->unsignedSmallInteger('jumlah_pegawai_perbaikan')->nullable();
            $table->text('catatan_evaluasi')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemantauan_iru', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_pegawai_perbaikan',
                'jumlah_pegawai_perbaikan',
                'catatan_evaluasi',
            ]);
        });

        Schema::table('progres_mitigasi', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['diverifikasi_oleh']);
            $table->dropColumn([
                'user_id',
                'bukti_path',
                'status_verifikasi',
                'diverifikasi_oleh',
                'diverifikasi_pada',
                'catatan_verifikasi',
            ]);
        });

        Schema::table('rencana_mitigasi', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['diverifikasi_oleh']);
            $table->dropColumn([
                'user_id',
                'bukti_path',
                'status_verifikasi',
                'diverifikasi_oleh',
                'diverifikasi_pada',
                'catatan_verifikasi',
            ]);
        });
    }
};
