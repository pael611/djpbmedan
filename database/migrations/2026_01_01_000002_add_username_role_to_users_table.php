<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('name');
            $table->enum('role', ['ADMIN', 'SKKI', 'UMUM', 'PPA_I', 'PPA_II', 'PAPK'])
                  ->default('UMUM')->after('password');
            $table->foreignId('bidang_id')->nullable()->after('role')
                  ->constrained('bidang')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bidang_id');
            $table->dropColumn(['username', 'role']);
        });
    }
};
