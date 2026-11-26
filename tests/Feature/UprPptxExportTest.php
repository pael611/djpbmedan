<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\NilaiAwalRisiko;
use App\Models\Risiko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UprPptxExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_combined_rapat_upr_pptx(): void
    {
        $bidang = Bidang::create(['kode' => 'UMUM', 'nama' => 'Umum']);
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin-pptx',
            'email' => 'admin-pptx@example.test',
            'password' => 'password',
            'role' => 'ADMIN',
        ]);

        $kejadian = Risiko::DAFTAR_KEJADIAN_RISIKO[9];
        $risiko = Risiko::factory()->create([
            'bidang_id' => $bidang->id,
            'sasaran' => 'Pengelolaan keuangan yang akuntabel dan BMN produktif',
            'kejadian_risiko' => $kejadian,
            'level_kemungkinan' => 3,
            'level_dampak' => 4,
            'triwulan' => 3,
            'tahun' => 2026,
        ]);

        NilaiAwalRisiko::create([
            'tahun' => 2026,
            'kejadian_risiko' => $kejadian,
            'nilai_awal' => 18,
            'updated_by' => $admin->id,
        ]);

        $rencana = $risiko->rencana()->create([
            'user_id' => $admin->id,
            'deskripsi_rencana' => '<ol><li>Mengajukan SKPT ke Kantor Pertanahan</li><li>Menyampaikan surat peringatan pertama</li></ol>',
            'status_verifikasi' => 'approved',
        ]);

        $rencana->progres()->create([
            'user_id' => $admin->id,
            'deskripsi_progres' => '<p>Melaksanakan One on One Meeting dengan penghuni rumah dinas.</p>',
            'bulan' => 9,
            'tahun' => 2026,
            'status_verifikasi' => 'approved',
        ]);

        $risiko->rekomendasiRisiko()->create([
            'triwulan' => 4,
            'tahun' => 2026,
            'deskripsi_rekomendasi' => 'Mempertahankan pengawasan dan tindak lanjut penertiban.',
            'rekomendasi_level_kemungkinan' => 3,
            'rekomendasi_level_dampak' => 4,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard.export-pptx', [
            'tahun' => 2026,
            'bulan' => 10,
        ]));

        $response
            ->assertOk()
            ->assertHeader(
                'Content-Type',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            )
            ->assertDownload('rapat-upr-kanwil-2026-bulan-10.pptx');

        // File .pptx adalah arsip ZIP OpenXML yang diawali signature "PK"
        $this->assertStringStartsWith('PK', $response->getFile()->getContent());
    }

    public function test_non_admin_cannot_download_rapat_upr_pptx(): void
    {
        $bidang = Bidang::create(['kode' => 'SKKI', 'nama' => 'SKKI']);
        $skki = User::create([
            'name' => 'SKKI User',
            'username' => 'skki-pptx',
            'email' => 'skki-pptx@example.test',
            'password' => 'password',
            'role' => 'SKKI',
            'bidang_id' => $bidang->id,
        ]);

        $this->actingAs($skki)
            ->get(route('dashboard.export-pptx', ['tahun' => 2026]))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_before_exporting_pptx(): void
    {
        $this->get(route('dashboard.export-pptx'))
            ->assertRedirect(route('login'));
    }
}
