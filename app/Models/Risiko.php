<?php

namespace App\Models;

use App\Traits\HitungMatriksRisiko;
use Database\Factories\RisikoFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Risiko extends Model
{
    /** @use HasFactory<RisikoFactory> */
    use HasFactory;

    use HitungMatriksRisiko;

    public const KATEGORI_RISIKO = [
        'Kebijakan' => 1,
        'Reputasi' => 2,
        'Fraud' => 3,
        'Legal' => 4,
        'Kepatuhan' => 5,
        'Operasional' => 6,
    ];

    public const JENIS_RISIKO = [
        'downside' => 'Downside Risk',
        'upside' => 'Upside Risk',
    ];

    protected $attributes = [
        'jenis_risiko' => 'downside',
    ];

    protected $table = 'risiko';

    protected $fillable = [
        'bidang_id', 'sasaran', 'kejadian_risiko', 'jenis_risiko',
        'level_kemungkinan', 'level_dampak',
        'besaran_risiko', 'warna_risiko',
        'triwulan', 'tahun', 'kategori_risiko', 'bobot_area_dampak',
    ];

    protected static function booted(): void
    {
        // Hitung otomatis setiap kali disimpan
        static::saving(function (Risiko $risiko) {
            $risiko->besaran_risiko = self::hitungBesaran(
                (int) $risiko->level_kemungkinan,
                (int) $risiko->level_dampak
            );
            $risiko->warna_risiko = self::klasifikasi($risiko->besaran_risiko)['kode'];
        });
    }

    // ---------- Relasi ----------
    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class);
    }

    public function rencana(): HasMany
    {
        return $this->hasMany(RencanaMitigasi::class, 'risiko_id');
    }

    public function rekomendasiRisiko(): HasMany
    {
        return $this->hasMany(RekomendasiRisiko::class, 'risiko_id');
    }

    public function indikatorIru(): HasMany
    {
        return $this->hasMany(IndikatorRisiko::class);
    }

    public function kejadianLed(): HasMany
    {
        return $this->hasMany(KejadianLed::class);
    }

    // ---------- Helper ----------
    public function getKlasifikasiAttribute(): array
    {
        return self::klasifikasi($this->besaran_risiko);
    }

    /** Rekomendasi terbaru yang memiliki level kemungkinan dan dampak. */
    public function getProyeksiAttribute(): ?RekomendasiRisiko
    {
        $rekomendasi = $this->relationLoaded('rekomendasiRisiko')
            ? $this->getRelation('rekomendasiRisiko')
            : $this->rekomendasiRisiko()->get();

        return $rekomendasi
            ->filter(fn (RekomendasiRisiko $item): bool => $item->rekomendasi_level_kemungkinan
                && $item->rekomendasi_level_dampak)
            ->sortByDesc(fn (RekomendasiRisiko $item): int => (int) $item->tahun * 10 + (int) $item->triwulan)
            ->first();
    }

    public function bolehDiaksesOleh(User $user): bool
    {
        return $user->isPrivileged() || $user->bidang_id === $this->bidang_id;
    }

    #[Scope]
    protected function orderByPriority(Builder $query): void
    {
        $categoryOrder = 'CASE';
        foreach (self::KATEGORI_RISIKO as $category => $order) {
            $categoryOrder .= ' WHEN kategori_risiko = ? THEN '.$order;
        }

        $query
            ->orderByDesc('besaran_risiko')
            ->orderByRaw('CASE WHEN bobot_area_dampak IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('bobot_area_dampak')
            ->orderByRaw($categoryOrder.' ELSE 7 END', array_keys(self::KATEGORI_RISIKO))
            ->orderBy('id');
    }
}
