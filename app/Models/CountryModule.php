<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CountryModule extends Pivot
{
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    

}
