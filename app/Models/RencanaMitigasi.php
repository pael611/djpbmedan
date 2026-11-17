<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RencanaMitigasi extends Model
{
    protected $table = 'rencana_mitigasi';

    protected $fillable = ['risiko_id', 'deskripsi_rencana'];

    public function risiko(): BelongsTo
    {
        return $this->belongsTo(Risiko::class, 'risiko_id');
    }

    public function progres(): HasMany
    {
        return $this->hasMany(ProgresMitigasi::class, 'rencana_id')->orderBy('tahun')->orderBy('bulan');
    }
}
