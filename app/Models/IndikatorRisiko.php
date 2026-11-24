<?php

namespace App\Models;

use Database\Factories\IndikatorRisikoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndikatorRisiko extends Model
{
    /** @use HasFactory<IndikatorRisikoFactory> */
    use HasFactory;

    public const JENIS_KONSOLIDASI = [
        'tidak_ada' => 'Tidak ada',
        'tlk' => 'Take last Known Value (TLK)',
    ];

    protected $table = 'indikator_risiko';

    protected $attributes = [
        'jenis_konsolidasi' => 'tidak_ada',
    ];

    protected $fillable = [
        'risiko_id',
        'nama_indikator',
        'definisi',
        'satuan',
        'jenis_konsolidasi',
        'formula',
        'polarisasi',
        'batas_aman',
        'batas_aman_bawah',
        'batas_aman_atas',
        'batas_atas',
        'batas_bawah',
        'periode_pemantauan',
        'penanggung_jawab',
        'penyedia_data',
        'sumber_data',
    ];

    public function risiko(): BelongsTo
    {
        return $this->belongsTo(Risiko::class);
    }

    public function pemantauanIru(): HasMany
    {
        return $this->hasMany(PemantauanIru::class);
    }

    public function statusUntuk(float $nilaiAktual): string
    {
        return match ($this->polarisasi) {
            'minimize' => $this->statusMinimize($nilaiAktual),
            'maximize' => $this->statusMaximize($nilaiAktual),
            'stabilize' => $this->statusStabilize($nilaiAktual),
            default => throw new \LogicException('Polarisasi IRU tidak valid.'),
        };
    }

    public function formatAngka(float|int|string|null $nilai): string
    {
        if ($nilai === null || $nilai === '') {
            return '—';
        }

        $formatted = rtrim(rtrim(number_format((float) $nilai, 4, '.', ''), '0'), '.');

        return $formatted === '-0' ? '0' : $formatted;
    }

    public function labelBatasAman(): string
    {
        if ($this->polarisasi === 'stabilize') {
            $atas = $this->formatAngka($this->batas_aman_atas);
            $bawah = $this->formatAngka($this->batas_aman_bawah);

            return "A: {$atas} / B: {$bawah}";
        }

        return $this->formatAngka($this->batas_aman);
    }

    public function labelBatasAtas(): string
    {
        if ($this->polarisasi === 'maximize') {
            return '—';
        }

        return $this->formatAngka($this->batas_atas);
    }

    public function labelBatasBawah(): string
    {
        if ($this->polarisasi === 'minimize') {
            return '—';
        }

        return $this->formatAngka($this->batas_bawah);
    }

    /**
     * Menyusun tabel riwayat IRU 3 tahun (Y-2, Y-1, Y) per Triwulan I-IV.
     *
     * @return array<int, array{
     *     triwulan: int,
     *     label: string,
     *     y_minus_2: array{bm: string, ba: string, bb: string, aktual: string},
     *     y_minus_1: array{bm: string, ba: string, bb: string, aktual: string},
     *     y: array{bm: string, ba: string, bb: string}
     * }>
     */
    public function riwayatTriwulanan(int $tahun): array
    {
        $pemantauan = $this->relationLoaded('pemantauanIru')
            ? $this->getRelation('pemantauanIru')
            : $this->pemantauanIru()->orderByDesc('tanggal_pengukuran')->get();

        $aktualByYearQuarter = [];
        foreach ($pemantauan as $item) {
            if (! $item->tanggal_pengukuran) {
                continue;
            }
            $key = $item->tanggal_pengukuran->year.'-'.$item->tanggal_pengukuran->quarter;
            if (! array_key_exists($key, $aktualByYearQuarter)) {
                $aktualByYearQuarter[$key] = $this->formatAngka($item->nilai_aktual);
            }
        }

        $bm = $this->labelBatasAman();
        $ba = $this->labelBatasAtas();
        $bb = $this->labelBatasBawah();
        $romawi = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];

        $rows = [];
        foreach ($romawi as $triwulan => $label) {
            $rows[$triwulan] = [
                'triwulan' => $triwulan,
                'label' => 'Triwulan '.$label,
                'y_minus_2' => [
                    'bm' => $bm,
                    'ba' => $ba,
                    'bb' => $bb,
                    'aktual' => $aktualByYearQuarter[($tahun - 2).'-'.$triwulan] ?? '—',
                ],
                'y_minus_1' => [
                    'bm' => $bm,
                    'ba' => $ba,
                    'bb' => $bb,
                    'aktual' => $aktualByYearQuarter[($tahun - 1).'-'.$triwulan] ?? '—',
                ],
                'y' => [
                    'bm' => $bm,
                    'ba' => $ba,
                    'bb' => $bb,
                ],
            ];
        }

        return $rows;
    }

    private function statusMinimize(float $nilaiAktual): string
    {
        if ($nilaiAktual <= (float) $this->batas_aman) {
            return 'Aman';
        }

        return $nilaiAktual <= (float) $this->batas_atas ? 'Waspada' : 'Awas';
    }

    private function statusMaximize(float $nilaiAktual): string
    {
        if ($nilaiAktual >= (float) $this->batas_aman) {
            return 'Aman';
        }

        return $nilaiAktual >= (float) $this->batas_bawah ? 'Waspada' : 'Awas';
    }

    private function statusStabilize(float $nilaiAktual): string
    {
        if ($nilaiAktual < (float) $this->batas_bawah
            || $nilaiAktual > (float) $this->batas_atas) {
            return 'Awas';
        }

        if ($nilaiAktual < (float) $this->batas_aman_bawah
            || $nilaiAktual > (float) $this->batas_aman_atas) {
            return 'Waspada';
        }

        return 'Aman';
    }
}
