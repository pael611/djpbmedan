<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermintaanPersetujuan extends Model
{
    public const SUBJECT_TYPES = [
        'risiko' => Risiko::class,
        'indikator' => IndikatorRisiko::class,
        'pengukuran' => PemantauanIru::class,
        'kejadian' => KejadianLed::class,
        'rekomendasi' => RekomendasiRisiko::class,
        'rencana' => RencanaMitigasi::class,
        'progres' => ProgresMitigasi::class,
    ];

    protected $fillable = [
        'subject_type',
        'subject_id',
        'action',
        'payload',
        'requested_by',
        'reviewed_by',
        'status',
        'review_note',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function subject(): ?Model
    {
        $model = self::SUBJECT_TYPES[$this->subject_type] ?? null;

        return $model ? $model::query()->find($this->subject_id) : null;
    }

    public function subjectRisk(): ?Risiko
    {
        $subject = $this->subject();

        return match (true) {
            $subject instanceof Risiko => $subject,
            $subject instanceof IndikatorRisiko => $subject->risiko,
            $subject instanceof PemantauanIru => $subject->indikatorRisiko->risiko,
            $subject instanceof KejadianLed => $subject->risiko,
            $subject instanceof RekomendasiRisiko => $subject->risiko,
            $subject instanceof RencanaMitigasi => $subject->risiko,
            $subject instanceof ProgresMitigasi => $subject->rencana->risiko,
            default => null,
        };
    }

    public function subjectDescription(): string
    {
        $subject = $this->subject();

        return match (true) {
            $subject instanceof Risiko => 'Risiko #'.$subject->id.' — '.$subject->kejadian_risiko,
            $subject instanceof IndikatorRisiko => 'Indikator IRU — '.$subject->nama_indikator,
            $subject instanceof PemantauanIru => 'Nilai aktual IRU — '.$subject->tanggal_pengukuran->format('d/m/Y'),
            $subject instanceof KejadianLed => 'Kejadian LED — '.$subject->uraian_kejadian,
            $subject instanceof RekomendasiRisiko => 'Rekomendasi risiko — '.$subject->deskripsi_rekomendasi,
            $subject instanceof RencanaMitigasi => 'Rencana mitigasi #'.$subject->id,
            $subject instanceof ProgresMitigasi => 'Progres mitigasi #'.$subject->id,
            default => 'Data sudah tidak tersedia',
        };
    }
}
