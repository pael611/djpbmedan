<?php

namespace Database\Factories;

use App\Models\IndikatorRisiko;
use App\Models\Risiko;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IndikatorRisiko>
 */
class IndikatorRisikoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'risiko_id' => Risiko::factory(),
            'nama_indikator' => fake()->unique()->sentence(3),
            'definisi' => fake()->sentence(),
            'satuan' => 'persen',
            'jenis_konsolidasi' => 'tidak_ada',
            'formula' => fake()->sentence(),
            'polarisasi' => 'minimize',
            'batas_aman' => 10,
            'batas_aman_bawah' => null,
            'batas_aman_atas' => null,
            'batas_atas' => 20,
            'batas_bawah' => null,
            'periode_pemantauan' => 'bulanan',
            'penanggung_jawab' => fake()->name(),
            'penyedia_data' => fake()->name(),
            'sumber_data' => fake()->sentence(),
        ];
    }
}
