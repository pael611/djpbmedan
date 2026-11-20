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

    public function isAdministrator(): bool
    {
        return $this->role === 'ADMIN';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'SKKI';
    }

    /** Admin and SKKI supervisors can manage and review all risk data. */
    public function isPrivileged(): bool
    {
        return $this->isAdministrator() || $this->isSupervisor();
    }
}
