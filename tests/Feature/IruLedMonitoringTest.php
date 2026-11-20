<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\PermintaanPersetujuan;
use App\Models\Risiko;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        Storage::fake('local');
        $risiko = Risiko::factory()->create(['level_kemungkinan' => 3]);
        $user = $this->userFor($risiko->bidang);

        $this->actingAs($user)
            ->post(route('kejadian-led.store', $risiko), [
                'terjadi_pada' => '2026-06-13 08:00:00',
                'uraian_kejadian' => 'Gangguan layanan',
                'dampak' => 'Pelayanan tertunda',
                'kejadian_luar_biasa' => true,
                'bukti_kejadian' => UploadedFile::fake()->create('bukti.pdf', 20, 'application/pdf'),
            ])
            ->assertRedirect();

        $event = $risiko->kejadianLed()->firstOrFail();
        $this->assertSame('pending', $event->status_verifikasi);
        $this->get(route('risiko.show', [
            'risiko' => $risiko,
            'triwulan' => 2,
            'tahun' => 2026,
        ]))->assertDontSee('Terdapat 1 kejadian LED terverifikasi');

        $admin = User::create([
            'name' => 'Supervisor',
            'username' => 'supervisor-led-review',
            'email' => 'supervisor-led-review@example.test',
            'password' => 'password',
            'role' => 'SKKI',
        ]);
        $this->actingAs($admin)
            ->get(route('persetujuan.index'))
            ->assertOk()
            ->assertSee('Lihat bukti kejadian')
            ->assertSee('Bukti sah')
            ->assertSee('Tolak bukti');

        $this->actingAs($admin)
            ->post(route('kejadian-led.verify', $event), [
                'status_verifikasi' => 'approved',
                'catatan_verifikasi' => 'Bukti sesuai.',
            ])
            ->assertRedirect();

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
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin-report-invalid',
            'email' => 'admin-report-invalid@example.test',
            'password' => 'password',
            'role' => 'ADMIN',
        ]);
        $event = $risiko->kejadianLed()->create([
            'user_id' => $user->id,
            'terjadi_pada' => '2026-06-13 08:00:00',
            'uraian_kejadian' => 'Gangguan layanan',
            'dampak' => 'Pelayanan tertunda',
            'kejadian_luar_biasa' => true,
        ]);

        $this->actingAs($admin)
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
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin-report-valid',
            'email' => 'admin-report-valid@example.test',
            'password' => 'password',
            'role' => 'ADMIN',
        ]);
        $event = $risiko->kejadianLed()->create([
            'user_id' => $user->id,
            'terjadi_pada' => '2026-06-13 08:00:00',
            'uraian_kejadian' => 'Gangguan layanan',
            'dampak' => 'Pelayanan tertunda',
            'kejadian_luar_biasa' => true,
        ]);

        $this->actingAs($admin)
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

    public function test_field_user_must_attach_evidence_to_an_led_submission(): void
    {
        $risiko = Risiko::factory()->create();
        $user = $this->userFor($risiko->bidang);

        $this->actingAs($user)
            ->from(route('risiko.show', $risiko))
            ->post(route('kejadian-led.store', $risiko), [
                'terjadi_pada' => now()->subHour()->format('Y-m-d H:i:s'),
                'uraian_kejadian' => 'Gangguan layanan',
                'dampak' => 'Pelayanan tertunda',
                'kejadian_luar_biasa' => false,
            ])
            ->assertSessionHasErrors('bukti_kejadian');

        $this->assertDatabaseCount('kejadian_led', 0);
    }

    public function test_field_user_risk_edit_is_applied_only_after_approval(): void
    {
        $risiko = Risiko::factory()->create(['kejadian_risiko' => 'Risiko awal']);
        $user = $this->userFor($risiko->bidang);

        $this->actingAs($user)
            ->put(route('risiko.update', $risiko), [
                'sasaran' => $risiko->sasaran,
                'kejadian_risiko' => 'Perubahan yang diajukan',
                'jenis_risiko' => $risiko->jenis_risiko,
                'level_kemungkinan' => $risiko->level_kemungkinan,
                'level_dampak' => $risiko->level_dampak,
                'triwulan' => $risiko->triwulan,
                'tahun' => $risiko->tahun,
                'kategori_risiko' => $risiko->kategori_risiko,
                'bobot_area_dampak' => $risiko->bobot_area_dampak,
            ])
            ->assertRedirect(route('risiko.show', $risiko))
            ->assertSessionHas('success', 'Perubahan risiko diajukan dan menunggu persetujuan Admin atau SKKI.');

        $this->assertSame('Risiko awal', $risiko->fresh()->kejadian_risiko);
        $request = PermintaanPersetujuan::query()->firstOrFail();
        $supervisor = User::create([
            'name' => 'Supervisor',
            'username' => 'supervisor-risk-edit',
            'email' => 'supervisor-risk-edit@example.test',
            'password' => 'password',
            'role' => 'SKKI',
        ]);

        $this->actingAs($supervisor)
            ->get(route('persetujuan.index'))
            ->assertOk()
            ->assertSee('Perubahan yang diajukan');

        $this->post(route('persetujuan.approve', $request))->assertRedirect();
        $this->assertSame('Perubahan yang diajukan', $risiko->fresh()->kejadian_risiko);
    }

    public function test_field_user_recommendation_edits_and_deletes_require_approval(): void
    {
        $risiko = Risiko::factory()->create();
        $user = $this->userFor($risiko->bidang);
        $recommendation = $risiko->rekomendasiRisiko()->create([
            'triwulan' => 1,
            'tahun' => 2026,
            'deskripsi_rekomendasi' => 'Rekomendasi supervisor.',
        ]);

        $this->actingAs($user)
            ->put(route('rekomendasi.update', $recommendation), [
                'triwulan' => 1,
                'tahun' => 2026,
                'deskripsi_rekomendasi' => 'Tidak boleh diubah.',
            ])
            ->assertRedirect();

        $this->assertSame('Rekomendasi supervisor.', $recommendation->fresh()->deskripsi_rekomendasi);
        $request = PermintaanPersetujuan::query()->firstOrFail();
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin-recommendation-approval',
            'email' => 'admin-recommendation-approval@example.test',
            'password' => 'password',
            'role' => 'ADMIN',
        ]);
        $this->actingAs($admin)->post(route('persetujuan.approve', $request))->assertRedirect();
        $this->assertSame('Tidak boleh diubah.', $recommendation->fresh()->deskripsi_rekomendasi);

        $this->actingAs($user)->delete(route('rekomendasi.destroy', $recommendation))->assertRedirect();
        $deleteRequest = PermintaanPersetujuan::query()->where('status', 'pending')->firstOrFail();
        $this->actingAs($admin)->post(route('persetujuan.approve', $deleteRequest))->assertRedirect();
        $this->assertDatabaseMissing('rekomendasi_risiko', ['id' => $recommendation->id]);
    }

    public function test_field_user_change_to_actual_iru_value_requires_supervisor_approval(): void
    {
        $risiko = Risiko::factory()->create();
        $user = $this->userFor($risiko->bidang);
        $indikator = $risiko->indikatorIru()->create($this->minimizeIndicatorData());
        $measurement = $indikator->pemantauanIru()->create([
            'user_id' => $user->id,
            'tanggal_pengukuran' => '2026-05-01',
            'nilai_aktual' => 15,
        ]);

        $this->actingAs($user)
            ->put(route('pemantauan-iru.update', $measurement), [
                'triwulan' => 2,
                'tahun' => 2026,
                'tanggal_pengukuran' => '2026-05-01',
                'nilai_aktual' => 9,
                'catatan' => 'Nilai dikoreksi.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Perubahan nilai aktual IRU diajukan untuk persetujuan Admin atau SKKI.');

        $this->assertSame('15.0000', $measurement->fresh()->nilai_aktual);

        $admin = User::create([
            'name' => 'Supervisor',
            'username' => 'supervisor-iru-approval',
            'email' => 'supervisor-iru-approval@example.test',
            'password' => 'password',
            'role' => 'SKKI',
        ]);
        $request = PermintaanPersetujuan::query()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('persetujuan.approve', $request))
            ->assertRedirect();

        $this->assertSame('9.0000', $measurement->fresh()->nilai_aktual);
        $this->assertDatabaseHas('permintaan_persetujuans', [
            'id' => $request->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
        ]);

        $this->actingAs($user)
            ->delete(route('pemantauan-iru.destroy', $measurement))
            ->assertRedirect();
        $this->assertDatabaseHas('pemantauan_iru', ['id' => $measurement->id]);
        $deleteRequest = PermintaanPersetujuan::query()->where('status', 'pending')->firstOrFail();
        $this->actingAs($admin)
            ->post(route('persetujuan.approve', $deleteRequest))
            ->assertRedirect();
        $this->assertDatabaseMissing('pemantauan_iru', ['id' => $measurement->id]);
    }

    public function test_field_user_cannot_create_mitigation_plans(): void
    {
        $risiko = Risiko::factory()->create();
        $user = $this->userFor($risiko->bidang);

        $this->actingAs($user)
            ->post(route('rencana.store', $risiko), ['deskripsi_rencana' => 'Rencana baru.'])
            ->assertForbidden();

        $this->assertDatabaseCount('rencana_mitigasi', 0);
    }

    public function test_supervisor_can_create_led_and_directly_edit_or_delete_iru_measurements(): void
    {
        $this->travelTo('2026-06-15 12:00:00');
        $risiko = Risiko::factory()->create();
        $supervisor = User::create([
            'name' => 'Supervisor',
            'username' => 'supervisor-direct-led',
            'email' => 'supervisor-direct-led@example.test',
            'password' => 'password',
            'role' => 'SKKI',
        ]);
        $indikator = $risiko->indikatorIru()->create($this->minimizeIndicatorData());
        $measurement = $indikator->pemantauanIru()->create([
            'tanggal_pengukuran' => '2026-05-01',
            'nilai_aktual' => 15,
        ]);

        $this->actingAs($supervisor)
            ->post(route('kejadian-led.store', $risiko), [
                'terjadi_pada' => '2026-06-14 08:00:00',
                'uraian_kejadian' => 'Gangguan layanan',
                'dampak' => 'Pelayanan tertunda',
                'kejadian_luar_biasa' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('kejadian_led', [
            'risiko_id' => $risiko->id,
            'status_verifikasi' => 'approved',
            'diverifikasi_oleh' => $supervisor->id,
        ]);

        $this->put(route('pemantauan-iru.update', $measurement), [
            'triwulan' => 2,
            'tahun' => 2026,
            'tanggal_pengukuran' => '2026-05-01',
            'nilai_aktual' => 9,
        ])->assertRedirect();

        $this->assertSame('9.0000', $measurement->fresh()->nilai_aktual);
        $this->delete(route('pemantauan-iru.destroy', $measurement))->assertRedirect();
        $this->assertDatabaseMissing('pemantauan_iru', ['id' => $measurement->id]);
        $this->assertDatabaseCount('permintaan_persetujuans', 0);
    }

    public function test_event_evidence_is_private_and_limited_to_the_same_field_or_a_manager(): void
    {
        Storage::fake('local');
        $risiko = Risiko::factory()->create();
        $user = $this->userFor($risiko->bidang);
        $otherFieldRisk = Risiko::factory()->create();
        $otherFieldUser = $this->userFor($otherFieldRisk->bidang);

        $this->actingAs($user)
            ->post(route('kejadian-led.store', $risiko), [
                'terjadi_pada' => now()->subHour()->format('Y-m-d H:i:s'),
                'uraian_kejadian' => 'Gangguan layanan',
                'dampak' => 'Pelayanan tertunda',
                'kejadian_luar_biasa' => false,
                'bukti_kejadian' => UploadedFile::fake()->create('bukti.pdf', 20, 'application/pdf'),
            ])
            ->assertRedirect();
        $event = $risiko->kejadianLed()->firstOrFail();

        $this->get(route('kejadian-led.evidence', $event))->assertOk();
        $this->actingAs($otherFieldUser)
            ->get(route('kejadian-led.evidence', $event))
            ->assertForbidden();

        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin-evidence-download',
            'email' => 'admin-evidence-download@example.test',
            'password' => 'password',
            'role' => 'ADMIN',
        ]);
        $this->actingAs($admin)
            ->get(route('kejadian-led.evidence', $event))
            ->assertOk();
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
