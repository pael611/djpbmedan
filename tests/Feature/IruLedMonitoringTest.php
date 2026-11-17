<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\Risiko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class IruLedMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_indicator_with_ordered_minimize_thresholds(): void
    {
        $risiko = Risiko::factory()->create();
        $user = $this->userFor($risiko->bidang);

        $this->actingAs($user)
            ->post(route('indikator-iru.store', $risiko), $this->minimizeIndicatorData())
            ->assertRedirect();

        $this->assertDatabaseHas('indikator_risiko', [
            'risiko_id' => $risiko->id,
            'nama_indikator' => 'Persentase kesalahan',
            'polarisasi' => 'minimize',
            'batas_aman' => 10,
            'batas_atas' => 20,
            'batas_bawah' => null,
        ]);
    }

    #[DataProvider('invalidThresholds')]
    public function test_indicator_rejects_thresholds_in_wrong_order(array $changes): void
    {
        $risiko = Risiko::factory()->create();
        $user = $this->userFor($risiko->bidang);
        $data = array_replace($this->minimizeIndicatorData(), $changes);

        $this->actingAs($user)
            ->from(route('risiko.show', $risiko))
            ->post(route('indikator-iru.store', $risiko), $data)
            ->assertSessionHasErrors([
                'polarisasi' => 'Urutan ambang harus konsisten dengan polarisasi dan rentang status IRU.',
            ]);

        $this->assertDatabaseCount('indikator_risiko', 0);
    }

    public static function invalidThresholds(): array
    {
        return [
            'minimize boundaries reversed' => [[
                'batas_aman' => 20,
                'batas_atas' => 10,
            ]],
            'maximize boundaries reversed' => [[
                'polarisasi' => 'maximize',
                'batas_aman' => 10,
                'batas_bawah' => 20,
            ]],
            'stabilize safe range reversed' => [[
                'polarisasi' => 'stabilize',
                'batas_aman_bawah' => 60,
                'batas_aman_atas' => 40,
                'batas_bawah' => 30,
                'batas_atas' => 70,
            ]],
        ];
    }

    public function test_indicator_rejects_thresholds_with_more_than_four_decimal_places(): void
    {
        $risiko = Risiko::factory()->create();
        $user = $this->userFor($risiko->bidang);
        $data = $this->minimizeIndicatorData();
        $data['batas_aman'] = '10.12345';

        $this->actingAs($user)
            ->from(route('risiko.show', $risiko))
            ->post(route('indikator-iru.store', $risiko), $data)
            ->assertSessionHasErrors('batas_aman');

        $this->assertDatabaseCount('indikator_risiko', 0);
    }

    public function test_user_cannot_create_indicator_for_another_field(): void
    {
        $risiko = Risiko::factory()->create();
        $user = $this->userFor(Bidang::factory()->create());

        $this->actingAs($user)
            ->post(route('indikator-iru.store', $risiko), $this->minimizeIndicatorData())
            ->assertForbidden();

        $this->assertDatabaseCount('indikator_risiko', 0);
    }

    public function test_waspada_iru_suggests_reviewing_likelihood_without_changing_risk(): void
    {
        $this->travelTo('2026-06-15 12:00:00');
        $risiko = Risiko::factory()->create(['level_kemungkinan' => 2]);
        $user = $this->userFor($risiko->bidang);
        $indikator = $risiko->indikatorIru()->create($this->minimizeIndicatorData());

        $this->actingAs($user)
            ->post(route('pemantauan-iru.store', $indikator), [
                'triwulan' => 2,
                'tahun' => 2026,
                'tanggal_pengukuran' => '2026-05-01',
                'nilai_aktual' => 15,
            ])
            ->assertRedirect();

        $this->get(route('risiko.show', [
            'risiko' => $risiko,
            'triwulan' => 2,
            'tahun' => 2026,
        ]))
            ->assertOk()
            ->assertSee('Waspada')
            ->assertSee('LK 2 → 3')
            ->assertSee('UPR tetap menetapkan LK');

        $this->assertSame(2, (int) $risiko->fresh()->level_kemungkinan);
    }

    public function test_measurement_date_must_match_selected_quarter(): void
    {
        $this->travelTo('2026-06-15 12:00:00');
        $risiko = Risiko::factory()->create();
        $user = $this->userFor($risiko->bidang);
        $indikator = $risiko->indikatorIru()->create($this->minimizeIndicatorData());

        $this->actingAs($user)
            ->from(route('risiko.show', $risiko))
            ->post(route('pemantauan-iru.store', $indikator), [
                'triwulan' => 2,
                'tahun' => 2026,
                'tanggal_pengukuran' => '2026-03-31',
                'nilai_aktual' => 15,
            ])
            ->assertSessionHasErrors('tanggal_pengukuran');

        $this->assertDatabaseCount('pemantauan_iru', 0);
    }

    public function test_latest_semiannual_iru_measurement_is_used_for_the_next_quarter(): void
    {
        $this->travelTo('2026-06-15 12:00:00');
        $risiko = Risiko::factory()->create(['level_kemungkinan' => 2]);
        $user = $this->userFor($risiko->bidang);
        $indikator = $risiko->indikatorIru()->create(array_replace(
            $this->minimizeIndicatorData(),
            ['periode_pemantauan' => 'semesteran']
        ));
        $indikator->pemantauanIru()->create([
            'tanggal_pengukuran' => '2026-03-31',
            'nilai_aktual' => 15,
        ]);

        $this->actingAs($user)
            ->get(route('risiko.show', [
                'risiko' => $risiko,
                'triwulan' => 2,
                'tahun' => 2026,
            ]))
            ->assertOk()
            ->assertSee('31/03/2026: 15.0000 (Waspada)')
            ->assertSee('LK 2 → 3');
    }

    public function test_led_event_triggers_review_and_flags_late_extraordinary_report(): void
    {
        $this->travelTo('2026-06-15 12:00:00');
        $risiko = Risiko::factory()->create(['level_kemungkinan' => 3]);
        $user = $this->userFor($risiko->bidang);

        $this->actingAs($user)
            ->post(route('kejadian-led.store', $risiko), [
                'terjadi_pada' => '2026-06-13 08:00:00',
                'uraian_kejadian' => 'Gangguan layanan',
                'dampak' => 'Pelayanan tertunda',
                'kejadian_luar_biasa' => true,
            ])
            ->assertRedirect();

        $event = $risiko->kejadianLed()->firstOrFail();

        $this->get(route('risiko.show', [
            'risiko' => $risiko,
            'triwulan' => 2,
            'tahun' => 2026,
        ]))
            ->assertOk()
            ->assertSee('Terdapat 1 kejadian LED')
            ->assertSee('LK 3 → 4')
            ->assertSee('Terlambat dilaporkan');

        $this->assertTrue($event->terlambatDilaporkan());
        $this->assertSame(3, (int) $risiko->fresh()->level_kemungkinan);
    }

    public function test_led_report_cannot_be_recorded_before_event_time(): void
    {
        $this->travelTo('2026-06-15 12:00:00');
        $risiko = Risiko::factory()->create();
        $user = $this->userFor($risiko->bidang);
        $event = $risiko->kejadianLed()->create([
            'user_id' => $user->id,
            'terjadi_pada' => '2026-06-13 08:00:00',
            'uraian_kejadian' => 'Gangguan layanan',
            'dampak' => 'Pelayanan tertunda',
            'kejadian_luar_biasa' => true,
        ]);

        $this->actingAs($user)
            ->from(route('risiko.show', $risiko))
            ->put(route('kejadian-led.report', $event), [
                'dilaporkan_pada' => '2026-06-13 07:59:00',
            ])
            ->assertSessionHasErrors('dilaporkan_pada');

        $this->assertNull($event->fresh()->dilaporkan_pada);
    }

    public function test_extraordinary_led_event_can_be_reported_within_one_day(): void
    {
        $this->travelTo('2026-06-15 12:00:00');
        $risiko = Risiko::factory()->create();
        $user = $this->userFor($risiko->bidang);
        $event = $risiko->kejadianLed()->create([
            'user_id' => $user->id,
            'terjadi_pada' => '2026-06-13 08:00:00',
            'uraian_kejadian' => 'Gangguan layanan',
            'dampak' => 'Pelayanan tertunda',
            'kejadian_luar_biasa' => true,
        ]);

        $this->actingAs($user)
            ->put(route('kejadian-led.report', $event), [
                'dilaporkan_pada' => '2026-06-14 08:00:00',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('kejadian_led', [
            'id' => $event->id,
            'dilaporkan_pada' => '2026-06-14 08:00:00',
        ]);
        $this->assertFalse($event->fresh()->terlambatDilaporkan());
    }

    public function test_user_cannot_create_led_event_for_another_field(): void
    {
        $risiko = Risiko::factory()->create();
        $user = $this->userFor(Bidang::factory()->create());

        $this->actingAs($user)
            ->post(route('kejadian-led.store', $risiko), [
                'terjadi_pada' => now()->subHour()->format('Y-m-d H:i:s'),
                'uraian_kejadian' => 'Gangguan layanan',
                'dampak' => 'Pelayanan tertunda',
                'kejadian_luar_biasa' => false,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('kejadian_led', 0);
    }

    private function userFor(Bidang $bidang): User
    {
        return User::create([
            'name' => 'Pengguna Bidang',
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => 'UMUM',
            'bidang_id' => $bidang->id,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function minimizeIndicatorData(): array
    {
        return [
            'nama_indikator' => 'Persentase kesalahan',
            'definisi' => 'Rasio kesalahan penyelesaian pekerjaan.',
            'satuan' => 'persen',
            'formula' => 'jumlah kesalahan / total pekerjaan',
            'polarisasi' => 'minimize',
            'batas_aman' => 10,
            'batas_atas' => 20,
            'periode_pemantauan' => 'bulanan',
            'penanggung_jawab' => 'Unit Pemilik Risiko',
            'penyedia_data' => 'Bidang terkait',
            'sumber_data' => 'Laporan bulanan',
        ];
    }
}
