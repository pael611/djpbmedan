<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\PermintaanPersetujuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RisikoTypeTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('riskTypes')]
    public function test_risk_type_is_persisted_when_a_risk_is_created(string $riskType): void
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
            'kejadian_risiko' => \App\Models\Risiko::DAFTAR_KEJADIAN_RISIKO[8],
            'jenis_risiko' => $riskType,
            'level_kemungkinan' => 3,
            'level_dampak' => 4,
            'triwulan' => 1,
            'tahun' => 2026,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('risiko', [
            'bidang_id' => $bidang->id,
            'jenis_risiko' => $riskType,
        ]);
    }

    public static function riskTypes(): array
    {
        return [
            'downside risk' => ['downside'],
            'upside risk' => ['upside'],
        ];
    }

    public function test_risk_type_can_be_changed_when_a_risk_is_updated(): void
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
            'kejadian_risiko' => \App\Models\Risiko::DAFTAR_KEJADIAN_RISIKO[2],
            'level_kemungkinan' => 3,
            'level_dampak' => 4,
            'triwulan' => 1,
            'tahun' => 2026,
        ]);

        $this->actingAs($user)
            ->put(route('risiko.update', $risiko), [
                'sasaran' => 'Meningkatkan kepatuhan',
                'kejadian_risiko' => \App\Models\Risiko::DAFTAR_KEJADIAN_RISIKO[8],
                'jenis_risiko' => 'upside',
                'level_kemungkinan' => 3,
                'level_dampak' => 4,
                'triwulan' => 1,
                'tahun' => 2026,
            ])
            ->assertRedirect(route('risiko.show', $risiko));

        $this->assertDatabaseHas('risiko', [
            'id' => $risiko->id,
            'jenis_risiko' => 'downside',
        ]);
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin-risk-type-approval',
            'email' => 'admin-risk-type-approval@example.test',
            'password' => 'password',
            'role' => 'ADMIN',
        ]);
        $request = PermintaanPersetujuan::query()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('persetujuan.approve', $request))
            ->assertRedirect();

        $this->assertDatabaseHas('risiko', [
            'id' => $risiko->id,
            'jenis_risiko' => 'upside',
        ]);
        $this->get(route('risiko.edit', $risiko))
            ->assertOk()
            ->assertSee('value="upside" selected', false);
    }

    #[DataProvider('invalidRiskTypes')]
    public function test_invalid_risk_type_is_rejected(?string $riskType): void
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
                'kejadian_risiko' => \App\Models\Risiko::DAFTAR_KEJADIAN_RISIKO[2],
                'jenis_risiko' => $riskType,
                'level_kemungkinan' => 3,
                'level_dampak' => 4,
                'triwulan' => 1,
                'tahun' => 2026,
            ])
            ->assertSessionHasErrors('jenis_risiko');

        $this->assertDatabaseCount('risiko', 0);
    }

    public static function invalidRiskTypes(): array
    {
        return [
            'missing risk type' => [null],
            'unknown risk type' => ['neutral'],
        ];
    }

    public function test_risk_form_explains_so_and_offers_both_risk_types(): void
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
            ->get(route('risiko.create'))
            ->assertOk()
            ->assertSee('name="jenis_risiko"', false)
            ->assertSee('Downside Risk')
            ->assertSee('Upside Risk')
            ->assertSee('Peristiwa tanpa dampak terhadap SO bukan risiko.');
    }

    public function test_matrix_uses_a_star_for_downside_and_an_oval_for_upside(): void
    {
        $bidang = Bidang::create(['kode' => 'A', 'nama' => 'Bidang A']);
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin-matrix-risk-types',
            'email' => 'admin-matrix-risk-types@example.test',
            'password' => 'password',
            'role' => 'ADMIN',
        ]);

        $attributes = [
            'sasaran' => 'Meningkatkan kepatuhan',
            'level_kemungkinan' => 3,
            'level_dampak' => 4,
            'triwulan' => 1,
            'tahun' => now()->year,
        ];
        $bidang->risiko()->create($attributes + [
            'kejadian_risiko' => 'Downside event',
            'jenis_risiko' => 'downside',
        ]);
        $bidang->risiko()->create($attributes + [
            'kejadian_risiko' => 'Upside event',
            'jenis_risiko' => 'upside',
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard.index', ['tahun' => now()->year]))
            ->assertOk()
            ->assertSee('data-jenis-risiko="downside"', false)
            ->assertSee('data-jenis-risiko="upside"', false)
            ->assertSee('clip-path: polygon(50% 0%,', false)
            ->assertSee('rounded-full min-w-8 h-6', false);
    }
}
