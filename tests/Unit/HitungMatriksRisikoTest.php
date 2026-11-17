<?php

namespace Tests\Unit;

use App\Traits\HitungMatriksRisiko;
use PHPUnit\Framework\TestCase;

class HitungMatriksRisikoTest extends TestCase
{
    use HitungMatriksRisiko;

    public function test_scores_match_the_reference_risk_matrix(): void
    {
        $expectedScores = [
            1 => [1 => 1, 2 => 5, 3 => 10, 4 => 15, 5 => 20],
            2 => [1 => 2, 2 => 6, 3 => 11, 4 => 16, 5 => 21],
            3 => [1 => 3, 2 => 8, 3 => 13, 4 => 18, 5 => 23],
            4 => [1 => 4, 2 => 9, 3 => 14, 4 => 19, 5 => 24],
            5 => [1 => 7, 2 => 12, 3 => 17, 4 => 22, 5 => 25],
        ];

        foreach ($expectedScores as $kemungkinan => $scoresByDampak) {
            foreach ($scoresByDampak as $dampak => $expectedScore) {
                $this->assertSame($expectedScore, self::hitungBesaran($kemungkinan, $dampak));
            }
        }
    }

    public function test_score_color_ranges_match_the_reference_legend(): void
    {
        $expectedColors = [
            1 => ['biru', '#0D47A1', '#ffffff'],
            5 => ['biru', '#0D47A1', '#ffffff'],
            6 => ['hijau', '#1B5E20', '#ffffff'],
            11 => ['hijau', '#1B5E20', '#ffffff'],
            12 => ['kuning', '#F9A825', '#000000'],
            15 => ['kuning', '#F9A825', '#000000'],
            16 => ['oranye', '#E65100', '#ffffff'],
            19 => ['oranye', '#E65100', '#ffffff'],
            20 => ['merah', '#B71C1C', '#ffffff'],
            25 => ['merah', '#B71C1C', '#ffffff'],
        ];

        foreach ($expectedColors as $score => [$expectedCode, $expectedHex, $expectedText]) {
            $classification = self::klasifikasi($score);

            $this->assertSame($expectedCode, $classification['kode']);
            $this->assertSame($expectedHex, $classification['hex']);
            $this->assertSame($expectedText, $classification['teks']);
        }
    }

    public function test_rejects_levels_outside_the_reference_matrix(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        self::hitungBesaran(0, 4);
    }
}
