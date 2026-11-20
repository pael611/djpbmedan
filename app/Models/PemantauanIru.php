<?php

namespace App\Models;

use Database\Factories\PemantauanIruFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemantauanIru extends Model
{
    /** @use HasFactory<PemantauanIruFactory> */
    use HasFactory;

    protected $table = 'pemantauan_iru';

    protected $fillable = [
        'indikator_risiko_id',
        'user_id',
        'tanggal_pengukuran',
        'nilai_aktual',
        'catatan',
        'jenis_pegawai_perbaikan',
        'jumlah_pegawai_perbaikan',
        'catatan_evaluasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengukuran' => 'date',
            'nilai_aktual' => 'decimal:4',
        ];
    }

    public function indikatorRisiko(): BelongsTo
    {
        return $this->belongsTo(IndikatorRisiko::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusAttribute(): string
    {
        return $this->indikatorRisiko->statusUntuk((float) $this->nilai_aktual);
    }
}
