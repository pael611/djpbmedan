<?php

namespace App\Models;

use App\Traits\HitungMatriksRisiko;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekomendasiRisiko extends Model
{
    use HitungMatriksRisiko;

    protected $table = 'rekomendasi_risiko';

    protected $fillable = [
        'risiko_id',
        'triwulan',
        'tahun',
        'deskripsi_rekomendasi',
        'rekomendasi_level_kemungkinan',
        'rekomendasi_level_dampak',
        'rekomendasi_besaran',
    ];

    protected static function booted(): void
    {
        static::saving(function (RekomendasiRisiko $rekomendasi): void {
            if ($rekomendasi->rekomendasi_level_kemungkinan && $rekomendasi->rekomendasi_level_dampak) {
                $rekomendasi->rekomendasi_besaran = self::hitungBesaran(
                    (int) $rekomendasi->rekomendasi_level_kemungkinan,
                    (int) $rekomendasi->rekomendasi_level_dampak
                );
            } else {
                $rekomendasi->rekomendasi_besaran = null;
            }
        });
    }

    public function risiko(): BelongsTo
    {
        return $this->belongsTo(Risiko::class);
    }

    public function getKlasifikasiRekomendasiAttribute(): ?array
    {
        return $this->rekomendasi_besaran ? self::klasifikasi($this->rekomendasi_besaran) : null;
    }
}
