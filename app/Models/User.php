<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'username', 'email', 'password', 'role', 'status'];

    protected $hidden = ['password', 'remember_token'];

    private ?array $clusterIdCache = null;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }

    /** Cluster yang ditetapkan admin untuk akun staff. */
    public function clusters(): BelongsToMany
    {
        return $this->belongsToMany(Cluster::class, 'cluster_user');
    }

    /** ID cluster yang ditetapkan (di-cache per request). Admin tidak memakai daftar ini. */
    public function assignedClusterIds(): array
    {
        return $this->clusterIdCache ??= $this->clusters()->pluck('clusters.id')->all();
    }
}
