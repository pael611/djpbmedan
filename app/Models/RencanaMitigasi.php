<?php

namespace App\Models;

use App\Support\RichTextSanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RencanaMitigasi extends Model
{
    protected $attributes = [
        'status_verifikasi' => 'approved',
    ];

    protected $table = 'rencana_mitigasi';

    protected $fillable = [
        'risiko_id',
        'user_id',
        'deskripsi_rencana',
        'bukti_path',
        'status_verifikasi',
        'diverifikasi_oleh',
        'diverifikasi_pada',
        'catatan_verifikasi',
    ];

    public function getDeskripsiRencanaAttribute(?string $value): string
    {
        return RichTextSanitizer::sanitize($value ?? '');
    }

    public function risiko(): BelongsTo
    {
        return $this->belongsTo(Risiko::class, 'risiko_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function progres(): HasMany
    {
        return $this->hasMany(ProgresMitigasi::class, 'rencana_id')->orderBy('tahun')->orderBy('bulan');
    }
}
