<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomModulePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_level_price_id',
        'module_id',
        'price',
    ];

    public function customLevelPrice(): BelongsTo
    {
        return $this->belongsTo(CustomLevelPrice::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
