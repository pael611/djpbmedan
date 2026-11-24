<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RisikoPdfExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_filter_controls_submit_when_they_change(): void
    {
        Bidang::create(['kode' => 'SKKI', 'nama' => 'Seksi Kepatuhan Internal']);
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin-filter',
            'email' => 'admin-filter@example.test',
            'password' => 'password',
            'role' => 'ADMIN',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('dashboard.index', ['tahun' => 2026]));

        $response->assertOk();
        $this->assertSame(
            5,
            substr_count($response->getContent(), 'onchange="this.form.requestSubmit()"')
        );
    }

    public function test_admin_can_download_a_landscape_risk_report_pdf(): void
    {
        $bidang = Bidang::create(['kode' => 'SKKI', 'nama' => 'Seksi Kepatuhan Internal']);
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'role' => 'ADMIN',
            'bidang_id' => $bidang->id,
        ]);
        $risiko = $bidang->risiko()->create([
            'sasaran' => 'Meningkatkan kepatuhan',
            'kejadian_risiko' => 'Keterlambatan tindak lanjut',
            'level_kemungkinan' => 3,
            'level_dampak' => 4,
            'triwulan' => 4,
            'tahun' => 2026,
        ]);
        $this->assertSame(18, (int) $risiko->fresh()->besaran_risiko);

        $rencana = $risiko->rencana()->create([
            'deskripsi_rencana' => str_repeat(
                'Melakukan pemantauan berkala untuk menekan dampak risiko yang telah diidentifikasi. ',
                80
            ),
        ]);
        $rencana->progres()->create([
            'deskripsi_progres' => 'Pemantauan bulan September selesai',
            'bulan' => 9,
            'tahun' => 2026,
        ]);
        $risiko->rekomendasiRisiko()->create([
            'triwulan' => 4,
            'tahun' => 2026,
            'deskripsi_rekomendasi' => 'Meningkatkan pemantauan pada Triwulan IV.',
            'rekomendasi_level_kemungkinan' => 3,
            'rekomendasi_level_dampak' => 4,
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard.index', ['tahun' => 2026]))
            ->assertOk()
            ->assertSee('Matriks Analisis Risiko')
            ->assertSee('18');

        $this->get(route('risiko.laporan', ['tahun' => 2026]))
            ->assertOk()
            ->assertSee('Export PDF');

        $response = $this->get(route('risiko.export-pdf', [
            'bidang' => 'SKKI',
            'bulan_aktual' => 'September',
            'tahun_aktual' => 2026,
            'triwulan_rekomendasi' => 'IV',
            'tahun_rekomendasi' => 2026,
        ]));

        $response
            ->assertOk()
            ->assertDownload('laporan-risiko-skki-september-2026.pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertGreaterThan(1, preg_match_all('/\/Type\s*\/Page\b/', $response->getContent()));
    }

    public function test_non_admin_cannot_download_a_risk_report_pdf(): void
    {
        $user = User::create([
            'name' => 'SKKI User',
            'username' => 'skki',
            'email' => 'skki@example.test',
            'password' => 'password',
            'role' => 'SKKI',
        ]);

        $response = $this->actingAs($user)->get(route('risiko.export-pdf', [
            'bidang' => 'SKKI',
            'bulan_aktual' => 'September',
            'tahun_aktual' => 2026,
            'triwulan_rekomendasi' => 'IV',
            'tahun_rekomendasi' => 2026,
        ]));

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_before_exporting_a_risk_report(): void
    {
        $response = $this->get(route('risiko.export-pdf'));

        $response->assertRedirect(route('login'));
    }

    public function test_invalid_actual_month_is_rejected(): void
    {
        Bidang::create(['kode' => 'SKKI', 'nama' => 'Seksi Kepatuhan Internal']);
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'role' => 'ADMIN',
        ]);

        $response = $this->actingAs($admin)->get(route('risiko.export-pdf', [
            'bidang' => 'SKKI',
            'bulan_aktual' => 'Smarch',
            'tahun_aktual' => 2026,
            'triwulan_rekomendasi' => 'IV',
            'tahun_rekomendasi' => 2026,
        ]));

        $response->assertSessionHasErrors('bulan_aktual');
    }
}
