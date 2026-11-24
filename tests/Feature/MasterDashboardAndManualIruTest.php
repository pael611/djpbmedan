<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\IndikatorRisiko;
use App\Models\NilaiAwalRisiko;
use App\Models\PemantauanIru;
use App\Models\Risiko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDashboardAndManualIruTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_dashboard_displays_matrix_ten_risk_events_and_iru_history(): void
    {
        $bidang = Bidang::create(['kode' => 'SKKI', 'nama' => 'SKKI']);
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin-master',
            'email' => 'admin-master@example.test',
            'password' => 'password',
            'role' => 'ADMIN',
        ]);

        $kejadian1 = Risiko::DAFTAR_KEJADIAN_RISIKO[1];
        $risiko = Risiko::factory()->create([
            'bidang_id' => $bidang->id,
            'kejadian_risiko' => $kejadian1,
            'level_kemungkinan' => 4,
            'level_dampak' => 4,
            'triwulan' => 3,
            'tahun' => 2026,
        ]);

        NilaiAwalRisiko::create([
            'tahun' => 2026,
            'kejadian_risiko' => $kejadian1,
            'nilai_awal' => 19,
            'updated_by' => $admin->id,
        ]);

        $indikator = IndikatorRisiko::factory()->create([
            'risiko_id' => $risiko->id,
            'nama_indikator' => 'Persentase Deviasi RPD',
            'polarisasi' => 'minimize',
            'batas_aman' => 5,
            'batas_atas' => 10,
            'jenis_konsolidasi' => 'tlk',
        ]);

        PemantauanIru::create([
            'indikator_risiko_id' => $indikator->id,
            'user_id' => $admin->id,
            'tanggal_pengukuran' => '2025-08-15',
            'nilai_aktual' => 7.5,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard.index', [
            'tahun' => 2026,
            'bulan' => 9,
            'kejadian_risiko' => $kejadian1,
        ]));

        $response
            ->assertOk()
            ->assertSee('Matriks Analisis Risiko 5×5 — September 2026')
            ->assertSee('Daftar Kejadian Risiko')
            ->assertSee('P26')
            ->assertSee('Q3')
            ->assertSee($kejadian1)
            ->assertSee(Risiko::DAFTAR_KEJADIAN_RISIKO[10])
            ->assertSee('Persentase Deviasi RPD')
            ->assertSee('Y-2 (2024)')
            ->assertSee('Y-1 (2025)')
            ->assertSee('Y (2026)')
            ->assertSee('7.5');
    }

    public function test_only_admin_can_update_annual_baseline_value(): void
    {
        $bidang = Bidang::create(['kode' => 'SKKI', 'nama' => 'SKKI']);
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin-p26',
            'email' => 'admin-p26@example.test',
            'password' => 'password',
            'role' => 'ADMIN',
        ]);
        $skki = User::create([
            'name' => 'SKKI',
            'username' => 'skki-p26',
            'email' => 'skki-p26@example.test',
            'password' => 'password',
            'role' => 'SKKI',
            'bidang_id' => $bidang->id,
        ]);

        $kejadian = Risiko::DAFTAR_KEJADIAN_RISIKO[7];

        $this->actingAs($skki)
            ->put(route('dashboard.nilai-awal.update'), [
                'tahun' => 2027,
                'kejadian_risiko' => $kejadian,
                'nilai_awal' => 20,
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->put(route('dashboard.nilai-awal.update'), [
                'tahun' => 2027,
                'kejadian_risiko' => $kejadian,
                'nilai_awal' => 20,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('nilai_awal_risiko', [
            'tahun' => 2027,
            'kejadian_risiko' => $kejadian,
            'nilai_awal' => 20,
        ]);
    }

    public function test_risiko_store_rejects_free_text_outside_ten_enum_options(): void
    {
        $bidang = Bidang::create(['kode' => 'UMUM', 'nama' => 'Umum']);
        $user = User::create([
            'name' => 'User Umum',
            'username' => 'umum-enum',
            'email' => 'umum-enum@example.test',
            'password' => 'password',
            'role' => 'UMUM',
            'bidang_id' => $bidang->id,
        ]);

        $this->actingAs($user)
            ->from(route('risiko.create'))
            ->post(route('risiko.store'), [
                'sasaran' => 'Meningkatkan tata kelola',
                'kejadian_risiko' => 'Kejadian bebas tidak terdaftar',
                'jenis_risiko' => 'downside',
                'level_kemungkinan' => 2,
                'level_dampak' => 3,
                'triwulan' => 1,
                'tahun' => 2026,
            ])
            ->assertSessionHasErrors('kejadian_risiko');

        $this->assertDatabaseCount('risiko', 0);
    }

    public function test_manual_iru_pdf_can_be_exported_per_indicator_and_per_risk_event(): void
    {
        $bidang = Bidang::create(['kode' => 'PPA_I', 'nama' => 'PPA I']);
        $user = User::create([
            'name' => 'User PPA I',
            'username' => 'ppai-pdf',
            'email' => 'ppai-pdf@example.test',
            'password' => 'password',
            'role' => 'PPA_I',
            'bidang_id' => $bidang->id,
        ]);

        $kejadian = Risiko::DAFTAR_KEJADIAN_RISIKO[4];
        $risiko = Risiko::factory()->create([
            'bidang_id' => $bidang->id,
            'kejadian_risiko' => $kejadian,
            'tahun' => 2026,
            'triwulan' => 3,
        ]);

        $indikator = IndikatorRisiko::factory()->create([
            'risiko_id' => $risiko->id,
            'nama_indikator' => 'Rasio Gagal Salur TKDD',
            'polarisasi' => 'minimize',
            'batas_aman' => 1,
            'batas_atas' => 5,
            'jenis_konsolidasi' => 'tidak_ada',
        ]);

        $single = $this->actingAs($user)->get(route('indikator-iru.manual-pdf', [
            'indikatorRisiko' => $indikator,
            'tahun' => 2026,
        ]));
        $single->assertOk();
        $this->assertStringStartsWith('%PDF', $single->getContent());

        $byEvent = $this->actingAs($user)->get(route('risiko.manual-iru-pdf', [
            'kejadian_risiko' => $kejadian,
            'tahun' => 2026,
        ]));
        $byEvent->assertOk();
        $this->assertStringStartsWith('%PDF', $byEvent->getContent());
    }
}
