<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cluster extends Model
{
    protected $fillable = ['cluster_code', 'cluster_name', 'description', 'status'];

    public function receipts(): HasMany
    {
        return $this->hasMany(MaterialReceipt::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cluster_user');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }
}
