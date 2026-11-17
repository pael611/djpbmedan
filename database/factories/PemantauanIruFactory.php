<?php

namespace Database\Factories;

use App\Models\IndikatorRisiko;
use App\Models\PemantauanIru;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PemantauanIru>
 */
class PemantauanIruFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'indikator_risiko_id' => IndikatorRisiko::factory(),
            'user_id' => null,
            'tanggal_pengukuran' => now()->toDateString(),
            'nilai_aktual' => 5,
            'catatan' => fake()->sentence(),
        ];
    }
}
