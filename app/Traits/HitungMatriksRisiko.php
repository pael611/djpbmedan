<?php

namespace App\Traits;

/**
 * Logika Matriks Risiko 5x5.
 * Skor ditetapkan berdasarkan matriks risiko referensi.
 */
trait HitungMatriksRisiko
{
    private const BESARAN_MATRIKS = [
        1 => [1 => 1, 2 => 5, 3 => 10, 4 => 15, 5 => 20],
        2 => [1 => 2, 2 => 6, 3 => 11, 4 => 16, 5 => 21],
        3 => [1 => 3, 2 => 8, 3 => 13, 4 => 18, 5 => 23],
        4 => [1 => 4, 2 => 9, 3 => 14, 4 => 19, 5 => 24],
        5 => [1 => 7, 2 => 12, 3 => 17, 4 => 22, 5 => 25],
    ];

    public static function hitungBesaran(int $kemungkinan, int $dampak): int
    {
        if (! isset(self::BESARAN_MATRIKS[$kemungkinan][$dampak])) {
            throw new \InvalidArgumentException('Level kemungkinan dan dampak harus berada pada rentang 1–5.');
        }

        return self::BESARAN_MATRIKS[$kemungkinan][$dampak];
    }

    /**
     * @return array{label:string, kode:string, hex:string, teks:string}
     */
    public static function klasifikasi(int $besaran): array
    {
        return match (true) {
            $besaran >= 20 => ['label' => 'Sangat Tinggi', 'kode' => 'merah',  'hex' => '#B71C1C', 'teks' => '#ffffff'],
            $besaran >= 16 => ['label' => 'Tinggi',        'kode' => 'oranye', 'hex' => '#E65100', 'teks' => '#ffffff'],
            $besaran >= 12 => ['label' => 'Sedang',        'kode' => 'kuning', 'hex' => '#F9A825', 'teks' => '#000000'],
            $besaran >= 6  => ['label' => 'Rendah',        'kode' => 'hijau',  'hex' => '#1B5E20', 'teks' => '#ffffff'],
            default        => ['label' => 'Sangat Rendah', 'kode' => 'biru',   'hex' => '#0D47A1', 'teks' => '#ffffff'],
        };
    }
}
