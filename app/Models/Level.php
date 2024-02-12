<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Level extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'slug',
        'minimum_age',
        'maximum_age',
        'modules',
        'tier',
        'complete_price',
    ];

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'available_levels')
            ->withTimestamps();
    }

    public function institutes(): BelongsToMany
    {
        return $this->belongsToMany(Institute::class, 'institute_level')
            ->withPivot('institute_diferencial_percentage_price')
            ->withPivot('institute_diferencial_aditional_price')
            ->withPivot('institute_right_exam')
            ->withPivot('can_edit')
            ->withTimestamps();
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'level_country')
            ->withPivot('price_discounted')
            ->withPivot('price_right_exam')
            ->withTimestamps();
    }

    public function levelCountries()
    {
        return $this->hasMany(LevelCountry::class);
    }
}
