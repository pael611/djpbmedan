<?php

namespace Database\Factories;

use App\Models\KejadianLed;
use App\Models\Risiko;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KejadianLed>
 */
class KejadianLedFactory extends Factory
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
            'user_id' => null,
            'terjadi_pada' => now()->subDay(),
            'uraian_kejadian' => fake()->sentence(),
            'dampak' => fake()->sentence(),
            'tindakan_mitigasi' => fake()->sentence(),
            'kondisi_setelah_mitigasi' => fake()->sentence(),
            'kejadian_luar_biasa' => false,
            'dilaporkan_pada' => null,
        ];
    }
}
