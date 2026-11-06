<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    public $timestamps = false;

    protected $fillable = ['unit_code', 'unit_name', 'status'];

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class, 'default_unit_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(MaterialReceipt::class);
    }
}
