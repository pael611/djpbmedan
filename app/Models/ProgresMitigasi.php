<?php

namespace App\Models;

use App\Traits\HitungMatriksRisiko;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgresMitigasi extends Model
{
    use HitungMatriksRisiko;

    protected $table = 'progres_mitigasi';

    protected $fillable = [
        'rencana_id', 'deskripsi_progres',
        'rekomendasi_level_kemungkinan', 'rekomendasi_level_dampak', 'rekomendasi_besaran',
        'bulan', 'tahun', 'file_bukti',
    ];

    protected static function booted(): void
    {
        static::saving(function (ProgresMitigasi $p) {
            if ($p->rekomendasi_level_kemungkinan && $p->rekomendasi_level_dampak) {
                $p->rekomendasi_besaran = self::hitungBesaran(
                    (int) $p->rekomendasi_level_kemungkinan,
                    (int) $p->rekomendasi_level_dampak
                );
            } else {
                $p->rekomendasi_besaran = null;
            }
        });
    }

    public function rencana(): BelongsTo
    {
        return $this->belongsTo(RencanaMitigasi::class, 'rencana_id');
    }

    public function getKlasifikasiRekomendasiAttribute(): ?array
    {
        return $this->rekomendasi_besaran ? self::klasifikasi($this->rekomendasi_besaran) : null;
    }
}
