<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\RekomendasiRisiko;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RisikoRecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_update_manual_quarter_recommendation(): void
    {
        $bidang = Bidang::create(['kode' => 'SKKI', 'nama' => 'Seksi Kepatuhan Internal']);
        $user = User::create([
            'name' => 'Admin',
            'username' => 'admin-rekomendasi',
            'email' => 'admin-rekomendasi@example.test',
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
        $input = [
            'triwulan' => 4,
            'tahun' => 2026,
            'deskripsi_rekomendasi' => 'Melakukan pengawasan secara berkala.',
            'rekomendasi_level_kemungkinan' => 3,
            'rekomendasi_level_dampak' => 4,
        ];

        $this->actingAs($user)
            ->post(route('rekomendasi.store', $risiko), $input)
            ->assertRedirect(route('risiko.show', $risiko));

        $this->assertDatabaseHas('rekomendasi_risiko', [
            'risiko_id' => $risiko->id,
            'triwulan' => 4,
            'tahun' => 2026,
            'deskripsi_rekomendasi' => 'Melakukan pengawasan secara berkala.',
            'rekomendasi_besaran' => 18,
        ]);

        $recommendation = RekomendasiRisiko::query()->firstOrFail();
        $input['deskripsi_rekomendasi'] = 'Meningkatkan koordinasi dan evaluasi rutin.';
        $input['rekomendasi_level_kemungkinan'] = 2;
        $input['rekomendasi_level_dampak'] = 3;

        $this->put(route('rekomendasi.update', $recommendation), $input)
            ->assertRedirect(route('risiko.show', $risiko));

        $this->assertDatabaseHas('rekomendasi_risiko', [
            'id' => $recommendation->id,
            'deskripsi_rekomendasi' => 'Meningkatkan koordinasi dan evaluasi rutin.',
            'rekomendasi_besaran' => 11,
        ]);

        $this->get(route('risiko.show', $risiko))
            ->assertOk()
            ->assertSee('Rekomendasi Risiko Triwulanan')
            ->assertSee('Meningkatkan koordinasi dan evaluasi rutin.');
    }

    public function test_skki_can_enter_a_recommendation_for_another_field(): void
    {
        $bidang = Bidang::create(['kode' => 'UMUM', 'nama' => 'Bidang Umum']);
        $user = User::create([
            'name' => 'Pengguna SKKI',
            'username' => 'pengguna-skki',
            'email' => 'pengguna-skki@example.test',
            'password' => 'password',
            'role' => 'SKKI',
        ]);
        $risiko = $bidang->risiko()->create([
            'sasaran' => 'Meningkatkan kepatuhan',
            'kejadian_risiko' => 'Keterlambatan tindak lanjut',
            'level_kemungkinan' => 2,
            'level_dampak' => 3,
            'triwulan' => 1,
            'tahun' => 2026,
        ]);

        $this->actingAs($user)
            ->post(route('rekomendasi.store', $risiko), [
                'triwulan' => 2,
                'tahun' => 2026,
                'deskripsi_rekomendasi' => 'Menyusun langkah pengendalian.',
            ])
            ->assertRedirect(route('risiko.show', $risiko));

        $this->assertDatabaseHas('rekomendasi_risiko', [
            'risiko_id' => $risiko->id,
            'deskripsi_rekomendasi' => 'Menyusun langkah pengendalian.',
        ]);
    }

    public function test_user_cannot_create_recommendation_for_another_field(): void
    {
        $userBidang = Bidang::create(['kode' => 'A', 'nama' => 'Bidang A']);
        $riskBidang = Bidang::create(['kode' => 'B', 'nama' => 'Bidang B']);
        $user = User::create([
            'name' => 'Pengguna Bidang A',
            'username' => 'pengguna-a',
            'email' => 'pengguna-a@example.test',
            'password' => 'password',
            'role' => 'UMUM',
            'bidang_id' => $userBidang->id,
        ]);
        $risiko = $riskBidang->risiko()->create([
            'sasaran' => 'Meningkatkan kepatuhan',
            'kejadian_risiko' => 'Keterlambatan tindak lanjut',
            'level_kemungkinan' => 2,
            'level_dampak' => 3,
            'triwulan' => 1,
            'tahun' => 2026,
        ]);

        $this->actingAs($user)
            ->post(route('rekomendasi.store', $risiko), [
                'triwulan' => 2,
                'tahun' => 2026,
                'deskripsi_rekomendasi' => 'Tidak boleh disimpan.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('rekomendasi_risiko', 0);
    }

    #[DataProvider('rolesWithoutRecommendationAccess')]
    public function test_unprivileged_role_cannot_create_recommendation_for_own_field(string $role): void
    {
        $bidang = Bidang::create(['kode' => 'A', 'nama' => 'Bidang A']);
        $user = User::create([
            'name' => 'Pengguna '.$role,
            'username' => strtolower($role),
            'email' => strtolower($role).'@example.test',
            'password' => 'password',
            'role' => $role,
            'bidang_id' => $bidang->id,
        ]);
        $risiko = $bidang->risiko()->create([
            'sasaran' => 'Meningkatkan kepatuhan',
            'kejadian_risiko' => 'Keterlambatan tindak lanjut',
            'level_kemungkinan' => 2,
            'level_dampak' => 3,
            'triwulan' => 1,
            'tahun' => 2026,
        ]);

        $this->actingAs($user)
            ->post(route('rekomendasi.store', $risiko), [
                'triwulan' => 2,
                'tahun' => 2026,
                'deskripsi_rekomendasi' => 'Tidak boleh disimpan.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('rekomendasi_risiko', 0);
    }

    public static function rolesWithoutRecommendationAccess(): array
    {
        return [
            'umum' => ['UMUM'],
            'ppa satu' => ['PPA_I'],
            'ppa dua' => ['PPA_II'],
            'papk' => ['PAPK'],
        ];
    }

    public function test_unprivileged_user_can_only_request_recommendation_updates(): void
    {
        $bidang = Bidang::create(['kode' => 'A', 'nama' => 'Bidang A']);
        $user = User::create([
            'name' => 'Pengguna Bidang',
            'username' => 'pengguna-bidang',
            'email' => 'pengguna-bidang@example.test',
            'password' => 'password',
            'role' => 'UMUM',
            'bidang_id' => $bidang->id,
        ]);
        $risiko = $bidang->risiko()->create([
            'sasaran' => 'Meningkatkan kepatuhan',
            'kejadian_risiko' => 'Keterlambatan tindak lanjut',
            'level_kemungkinan' => 2,
            'level_dampak' => 3,
            'triwulan' => 1,
            'tahun' => 2026,
        ]);
        $rekomendasi = $risiko->rekomendasiRisiko()->create([
            'triwulan' => 2,
            'tahun' => 2026,
            'deskripsi_rekomendasi' => 'Rekomendasi awal.',
        ]);

        $this->actingAs($user)
            ->put(route('rekomendasi.update', $rekomendasi), [
                'triwulan' => 2,
                'tahun' => 2026,
                'deskripsi_rekomendasi' => 'Tidak boleh diperbarui.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Perubahan rekomendasi diajukan untuk persetujuan Admin atau SKKI.');

        $this->assertDatabaseHas('rekomendasi_risiko', [
            'id' => $rekomendasi->id,
            'deskripsi_rekomendasi' => 'Rekomendasi awal.',
        ]);
        $this->assertDatabaseHas('permintaan_persetujuans', [
            'subject_type' => 'rekomendasi',
            'subject_id' => $rekomendasi->id,
            'action' => 'update',
            'status' => 'pending',
        ]);
    }

    public function test_unprivileged_user_can_request_recommendation_changes(): void
    {
        $bidang = Bidang::create(['kode' => 'A', 'nama' => 'Bidang A']);
        $user = User::create([
            'name' => 'Pengguna Bidang',
            'username' => 'pengguna-bidang',
            'email' => 'pengguna-bidang@example.test',
            'password' => 'password',
            'role' => 'UMUM',
            'bidang_id' => $bidang->id,
        ]);
        $risiko = $bidang->risiko()->create([
            'sasaran' => 'Meningkatkan kepatuhan',
            'kejadian_risiko' => 'Keterlambatan tindak lanjut',
            'level_kemungkinan' => 2,
            'level_dampak' => 3,
            'triwulan' => 1,
            'tahun' => 2026,
        ]);
        $risiko->rekomendasiRisiko()->create([
            'triwulan' => 2,
            'tahun' => 2026,
            'deskripsi_rekomendasi' => 'Rekomendasi hanya-baca.',
        ]);

        $this->actingAs($user)
            ->get(route('risiko.show', $risiko))
            ->assertOk()
            ->assertSee('Rekomendasi hanya-baca.')
            ->assertSee('Ajukan perubahan rekomendasi')
            ->assertSee('Ajukan Hapus');
    }

    public function test_duplicate_risk_quarter_recommendation_is_rejected(): void
    {
        $bidang = Bidang::create(['kode' => 'SKKI', 'nama' => 'Seksi Kepatuhan Internal']);
        $user = User::create([
            'name' => 'Admin',
            'username' => 'admin-duplicate',
            'email' => 'admin-duplicate@example.test',
            'password' => 'password',
            'role' => 'ADMIN',
            'bidang_id' => $bidang->id,
        ]);
        $risiko = $bidang->risiko()->create([
            'sasaran' => 'Meningkatkan kepatuhan',
            'kejadian_risiko' => 'Keterlambatan tindak lanjut',
            'level_kemungkinan' => 2,
            'level_dampak' => 3,
            'triwulan' => 1,
            'tahun' => 2026,
        ]);
        $risiko->rekomendasiRisiko()->create([
            'triwulan' => 4,
            'tahun' => 2026,
            'deskripsi_rekomendasi' => 'Rekomendasi awal.',
        ]);

        $this->actingAs($user)
            ->from(route('risiko.show', $risiko))
            ->post(route('rekomendasi.store', $risiko), [
                'triwulan' => 4,
                'tahun' => 2026,
                'deskripsi_rekomendasi' => 'Rekomendasi duplikat.',
            ])
            ->assertSessionHasErrors('triwulan');

        $this->assertDatabaseCount('rekomendasi_risiko', 1);
    }

    public function test_recommendation_risk_levels_must_be_entered_as_a_pair(): void
    {
        $bidang = Bidang::create(['kode' => 'SKKI', 'nama' => 'Seksi Kepatuhan Internal']);
        $user = User::create([
            'name' => 'Admin',
            'username' => 'admin-validation',
            'email' => 'admin-validation@example.test',
            'password' => 'password',
            'role' => 'ADMIN',
            'bidang_id' => $bidang->id,
        ]);
        $risiko = $bidang->risiko()->create([
            'sasaran' => 'Meningkatkan kepatuhan',
            'kejadian_risiko' => 'Keterlambatan tindak lanjut',
            'level_kemungkinan' => 2,
            'level_dampak' => 3,
            'triwulan' => 1,
            'tahun' => 2026,
        ]);

        $this->actingAs($user)
            ->from(route('risiko.show', $risiko))
            ->post(route('rekomendasi.store', $risiko), [
                'triwulan' => 4,
                'tahun' => 2026,
                'deskripsi_rekomendasi' => 'Rekomendasi tanpa pasangan level.',
                'rekomendasi_level_kemungkinan' => 3,
            ])
            ->assertSessionHasErrors('rekomendasi_level_dampak');

        $this->assertDatabaseCount('rekomendasi_risiko', 0);
    }

    public function test_pdf_receives_manual_recommendation_for_selected_quarter_only(): void
    {
        $bidang = Bidang::create(['kode' => 'SKKI', 'nama' => 'Seksi Kepatuhan Internal']);
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'role' => 'ADMIN',
        ]);
        $risiko = $bidang->risiko()->create([
            'sasaran' => 'Meningkatkan kepatuhan',
            'kejadian_risiko' => 'Keterlambatan tindak lanjut',
            'level_kemungkinan' => 3,
            'level_dampak' => 4,
            'triwulan' => 4,
            'tahun' => 2026,
        ]);
        $rencana = $risiko->rencana()->create(['deskripsi_rencana' => 'Pemantauan berkala.']);
        $rencana->progres()->create([
            'deskripsi_progres' => 'Progres bulanan, bukan rekomendasi triwulanan.',
            'bulan' => 10,
            'tahun' => 2026,
        ]);
        $risiko->rekomendasiRisiko()->create([
            'triwulan' => 4,
            'tahun' => 2026,
            'deskripsi_rekomendasi' => 'Rekomendasi manual Triwulan IV.',
            'rekomendasi_level_kemungkinan' => 3,
            'rekomendasi_level_dampak' => 4,
        ]);
        $risiko->rekomendasiRisiko()->create([
            'triwulan' => 3,
            'tahun' => 2026,
            'deskripsi_rekomendasi' => 'Rekomendasi triwulan sebelumnya.',
        ]);

        $document = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $document->shouldReceive('setPaper')->once()->with('a4', 'landscape')->andReturnSelf();
        $document->shouldReceive('download')->once()->andReturn(new Response('%PDF-test'));

        Pdf::shouldReceive('loadView')
            ->once()
            ->withArgs(function (string $view, array $data): bool {
                $this->assertSame('exports.risiko_pdf', $view);
                $this->assertSame(
                    'Rekomendasi manual Triwulan IV.',
                    $data['reports'][0]['rekomendasi']->first()->deskripsi_rekomendasi
                );
                $this->assertCount(1, $data['reports'][0]['rekomendasi']);

                return true;
            })
            ->andReturn($document);

        $this->actingAs($admin)
            ->get(route('risiko.export-pdf', [
                'bidang' => 'SKKI',
                'bulan_aktual' => 'September',
                'tahun_aktual' => 2026,
                'triwulan_rekomendasi' => 'IV',
                'tahun_rekomendasi' => 2026,
            ]))
            ->assertOk();
    }
}
