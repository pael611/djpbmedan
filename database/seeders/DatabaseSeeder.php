<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $bidang = [
            'UMUM'   => 'Umum',
            'PPA_I'  => 'PPA I',
            'PPA_II' => 'PPA II',
            'PAPK'   => 'PAPK',
            'SKKI'   => 'SKKI',
        ];

        foreach ($bidang as $kode => $nama) {
            Bidang::updateOrCreate(['kode' => $kode], ['nama' => $nama]);
        }

        // Admin (tanpa bidang)
        User::updateOrCreate(['username' => 'admin'], [
            'name' => 'Administrator', 'email' => 'admin@upr.test',
            'password' => 'password', 'role' => 'ADMIN', 'bidang_id' => null,
        ]);

        // Satu user per bidang (password: password) -> GANTI di production!
        foreach ($bidang as $kode => $nama) {
            User::updateOrCreate(['username' => strtolower($kode)], [
                'name' => "User {$nama}", 'email' => strtolower($kode) . '@upr.test',
                'password' => 'password', 'role' => $kode,
                'bidang_id' => Bidang::where('kode', $kode)->value('id'),
            ]);
        }
    }
}
