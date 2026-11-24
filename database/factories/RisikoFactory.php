<?php

namespace Database\Factories;

use App\Models\Bidang;
use App\Models\Risiko;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Risiko>
 */
class RisikoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bidang_id' => Bidang::factory(),
            'sasaran' => fake()->sentence(),
            'kejadian_risiko' => fake()->randomElement(Risiko::DAFTAR_KEJADIAN_RISIKO),
            'jenis_risiko' => 'downside',
            'level_kemungkinan' => fake()->numberBetween(1, 5),
            'level_dampak' => fake()->numberBetween(1, 5),
            'triwulan' => fake()->numberBetween(1, 4),
            'tahun' => now()->year,
            'kategori_risiko' => null,
            'bobot_area_dampak' => null,
        ];
    }
}
