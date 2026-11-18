<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\Risiko;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class RisikoPriorityTest extends TestCase
{
    use RefreshDatabase;

    public function test_risk_form_persists_optional_priority_attributes(): void
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

        $response = $this->actingAs($user)->post(route('risiko.store'), [
            'sasaran' => 'Meningkatkan kepatuhan',
            'kejadian_risiko' => 'Keterlambatan tindak lanjut',
            'jenis_risiko' => 'downside',
            'level_kemungkinan' => 3,
            'level_dampak' => 4,
            'triwulan' => 1,
            'tahun' => 2026,
            'kategori_risiko' => 'Kepatuhan',
            'bobot_area_dampak' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('risiko', [
            'bidang_id' => $bidang->id,
            'kategori_risiko' => 'Kepatuhan',
            'bobot_area_dampak' => 5,
        ]);
    }

    public function test_invalid_priority_attributes_are_rejected(): void
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

        $this->actingAs($user)
            ->from(route('risiko.create'))
            ->post(route('risiko.store'), [
                'sasaran' => 'Meningkatkan kepatuhan',
                'kejadian_risiko' => 'Keterlambatan tindak lanjut',
                'jenis_risiko' => 'downside',
                'level_kemungkinan' => 3,
                'level_dampak' => 4,
                'triwulan' => 1,
                'tahun' => 2026,
                'kategori_risiko' => 'Lainnya',
                'bobot_area_dampak' => 8,
            ])
            ->assertSessionHasErrors(['kategori_risiko', 'bobot_area_dampak']);

        $this->assertDatabaseCount('risiko', 0);
    }

    public function test_report_orders_equal_scores_by_impact_weight_then_category(): void
    {
        $bidang = Bidang::create(['kode' => 'A', 'nama' => 'Bidang A']);
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin-priority',
            'email' => 'admin-priority@example.test',
            'password' => 'password',
            'role' => 'ADMIN',
        ]);
        $this->createPrioritizedRisks($bidang);

        $this->actingAs($admin)
            ->get(route('risiko.laporan', ['tahun' => 2026]))
            ->assertOk()
            ->assertSeeInOrder([
                'Prioritas kategori satu',
                'Prioritas kategori dua',
                'Prioritas area enam',
            ]);
    }

    public function test_pdf_report_orders_equal_scores_by_impact_weight_then_category(): void
    {
        $bidang = Bidang::create(['kode' => 'A', 'nama' => 'Bidang A']);
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin-pdf-priority',
            'email' => 'admin-pdf-priority@example.test',
            'password' => 'password',
            'role' => 'ADMIN',
        ]);
        $this->createPrioritizedRisks($bidang);

        $document = \Mockery::mock(DomPdfDocument::class);
        $document->shouldReceive('setPaper')->once()->with('a4', 'landscape')->andReturnSelf();
        $document->shouldReceive('download')->once()->andReturn(new Response('%PDF-test'));
        Pdf::shouldReceive('loadView')
            ->once()
            ->withArgs(function (string $view, array $data): bool {
                $this->assertSame('exports.risiko_pdf', $view);
                $this->assertSame([
                    'Prioritas kategori satu',
                    'Prioritas kategori dua',
                    'Prioritas area enam',
                ], array_map(
                    fn (array $report): string => $report['risiko']->kejadian_risiko,
                    $data['reports']
                ));

                return true;
            })
            ->andReturn($document);

        $this->actingAs($admin)
            ->get(route('risiko.export-pdf', [
                'bidang' => 'A',
                'bulan_aktual' => 'September',
                'tahun_aktual' => 2026,
                'triwulan_rekomendasi' => 'IV',
                'tahun_rekomendasi' => 2026,
            ]))
            ->assertOk();
    }

    /**
     * @return array{0: Risiko, 1: Risiko, 2: Risiko}
     */
    private function createPrioritizedRisks(Bidang $bidang): array
    {
        $areaSix = $bidang->risiko()->create([
            'sasaran' => 'Sasaran area enam',
            'kejadian_risiko' => 'Prioritas area enam',
            'level_kemungkinan' => 4,
            'level_dampak' => 4,
            'triwulan' => 4,
            'tahun' => 2026,
            'kategori_risiko' => 'Kebijakan',
            'bobot_area_dampak' => 6,
        ]);
        $categoryTwo = $bidang->risiko()->create([
            'sasaran' => 'Sasaran kategori dua',
            'kejadian_risiko' => 'Prioritas kategori dua',
            'level_kemungkinan' => 4,
            'level_dampak' => 4,
            'triwulan' => 4,
            'tahun' => 2026,
            'kategori_risiko' => 'Reputasi',
            'bobot_area_dampak' => 7,
        ]);
        $categoryOne = $bidang->risiko()->create([
            'sasaran' => 'Sasaran kategori satu',
            'kejadian_risiko' => 'Prioritas kategori satu',
            'level_kemungkinan' => 4,
            'level_dampak' => 4,
            'triwulan' => 4,
            'tahun' => 2026,
            'kategori_risiko' => 'Kebijakan',
            'bobot_area_dampak' => 7,
        ]);

        return [$areaSix, $categoryTwo, $categoryOne];
    }
}
