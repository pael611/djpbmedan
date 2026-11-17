<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public const ROLES = ['ADMIN', 'SKKI', 'UMUM', 'PPA_I', 'PPA_II', 'PAPK'];

    protected $fillable = ['name', 'username', 'email', 'password', 'role', 'bidang_id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class);
    }

    /** Admin & SKKI boleh melihat Dashboard Master dan seluruh data risiko. */
    public function isPrivileged(): bool
    {
        return in_array($this->role, ['ADMIN', 'SKKI'], true);
    }
}
