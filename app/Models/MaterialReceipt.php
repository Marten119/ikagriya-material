<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialReceipt extends Model
{
    protected $fillable = [
        'transaction_number', 'transaction_date', 'cluster_id', 'material_id', 'volume',
        'unit_id', 'usage_per_unit', 'usage_m2', 'notes', 'submit_token', 'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'volume' => 'decimal:3',
        'usage_per_unit' => 'decimal:3',
        'usage_m2' => 'decimal:2',
    ];

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
