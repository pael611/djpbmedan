<?php

namespace Tests\Unit;

use App\Models\IndikatorRisiko;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class IndikatorRisikoStatusTest extends TestCase
{
    #[DataProvider('statusCases')]
    public function test_status_follows_polarization_thresholds(
        array $thresholds,
        float $nilaiAktual,
        string $expectedStatus
    ): void {
        $indikator = new IndikatorRisiko($thresholds);

        $this->assertSame($expectedStatus, $indikator->statusUntuk($nilaiAktual));
    }

    public static function statusCases(): array
    {
        return [
            'minimize at BM' => [
                ['polarisasi' => 'minimize', 'batas_aman' => 10, 'batas_atas' => 20],
                10,
                'Aman',
            ],
            'minimize at BA' => [
                ['polarisasi' => 'minimize', 'batas_aman' => 10, 'batas_atas' => 20],
                20,
                'Waspada',
            ],
            'minimize above BA' => [
                ['polarisasi' => 'minimize', 'batas_aman' => 10, 'batas_atas' => 20],
                20.01,
                'Awas',
            ],
            'maximize at BM' => [
                ['polarisasi' => 'maximize', 'batas_aman' => 10, 'batas_bawah' => 5],
                10,
                'Aman',
            ],
            'maximize at BB' => [
                ['polarisasi' => 'maximize', 'batas_aman' => 10, 'batas_bawah' => 5],
                5,
                'Waspada',
            ],
            'maximize below BB' => [
                ['polarisasi' => 'maximize', 'batas_aman' => 10, 'batas_bawah' => 5],
                4.99,
                'Awas',
            ],
            'stabilize at lower BM' => [
                [
                    'polarisasi' => 'stabilize',
                    'batas_aman_bawah' => 40,
                    'batas_aman_atas' => 60,
                    'batas_bawah' => 30,
                    'batas_atas' => 70,
                ],
                40,
                'Aman',
            ],
            'stabilize below safe range' => [
                [
                    'polarisasi' => 'stabilize',
                    'batas_aman_bawah' => 40,
                    'batas_aman_atas' => 60,
                    'batas_bawah' => 30,
                    'batas_atas' => 70,
                ],
                35,
                'Waspada',
            ],
            'stabilize at BB' => [
                [
                    'polarisasi' => 'stabilize',
                    'batas_aman_bawah' => 40,
                    'batas_aman_atas' => 60,
                    'batas_bawah' => 30,
                    'batas_atas' => 70,
                ],
                30,
                'Waspada',
            ],
            'stabilize below BB' => [
                [
                    'polarisasi' => 'stabilize',
                    'batas_aman_bawah' => 40,
                    'batas_aman_atas' => 60,
                    'batas_bawah' => 30,
                    'batas_atas' => 70,
                ],
                29.99,
                'Awas',
            ],
            'stabilize within safe range' => [
                [
                    'polarisasi' => 'stabilize',
                    'batas_aman_bawah' => 40,
                    'batas_aman_atas' => 60,
                    'batas_bawah' => 30,
                    'batas_atas' => 70,
                ],
                50,
                'Aman',
            ],
            'stabilize at BA' => [
                [
                    'polarisasi' => 'stabilize',
                    'batas_aman_bawah' => 40,
                    'batas_aman_atas' => 60,
                    'batas_bawah' => 30,
                    'batas_atas' => 70,
                ],
                70,
                'Waspada',
            ],
            'stabilize above BA' => [
                [
                    'polarisasi' => 'stabilize',
                    'batas_aman_bawah' => 40,
                    'batas_aman_atas' => 60,
                    'batas_bawah' => 30,
                    'batas_atas' => 70,
                ],
                70.01,
                'Awas',
            ],
        ];
    }
}
