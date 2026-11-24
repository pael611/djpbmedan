<?php

namespace App\Models;

use App\Traits\HitungMatriksRisiko;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiAwalRisiko extends Model
{
    use HitungMatriksRisiko;

    protected $table = 'nilai_awal_risiko';

    protected $fillable = [
        'tahun',
        'kejadian_risiko',
        'nilai_awal',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'nilai_awal' => 'integer',
        ];
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @return array{label:string, kode:string, hex:string, teks:string}
     */
    public function getKlasifikasiAttribute(): array
    {
        return self::klasifikasi($this->nilai_awal);
    }
}
