<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LevelCountry extends Model
{
    protected $table = 'level_country';

    protected $fillable = [
        'level_id',
        'country_id',
        'price_discounted',
        'price_right_exam',
    ];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'level_country_module')
            ->withPivot('price')
            ->withTimestamps();
    }

    public function levelCountryModules(): HasMany
    {
        return $this->hasMany(LevelCountryModule::class);
    }
}
