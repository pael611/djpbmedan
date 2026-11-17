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

    protected $table = 'indikator_risiko';

    protected $fillable = [
        'risiko_id',
        'nama_indikator',
        'definisi',
        'satuan',
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
