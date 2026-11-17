<?php

namespace App\Models;

use Database\Factories\KejadianLedFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KejadianLed extends Model
{
    /** @use HasFactory<KejadianLedFactory> */
    use HasFactory;

    protected $table = 'kejadian_led';

    protected $fillable = [
        'risiko_id',
        'user_id',
        'terjadi_pada',
        'uraian_kejadian',
        'dampak',
        'tindakan_mitigasi',
        'kondisi_setelah_mitigasi',
        'kejadian_luar_biasa',
        'dilaporkan_pada',
    ];

    protected function casts(): array
    {
        return [
            'terjadi_pada' => 'datetime',
            'dilaporkan_pada' => 'datetime',
            'kejadian_luar_biasa' => 'boolean',
        ];
    }

    public function risiko(): BelongsTo
    {
        return $this->belongsTo(Risiko::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function terlambatDilaporkan(): bool
    {
        return $this->kejadian_luar_biasa
            && ($this->dilaporkan_pada
                ? $this->dilaporkan_pada->greaterThan($this->terjadi_pada->copy()->addDay())
                : now()->greaterThan($this->terjadi_pada->copy()->addDay()));
    }
}
