<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

// https://filamentphp.com/docs/3.x/forms/fields/repeater#integrating-with-a-belongstomany-eloquent-relationship
class LevelCountryModule extends Pivot
{
    protected $fillable = [
        'level_country_id',
        'module_id',
        'price',
    ];

    public function levelCountry(): BelongsTo
    {
        return $this->belongsTo(LevelCountry::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
