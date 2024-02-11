<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Level extends Model 
{
    use HasFactory, Sluggable, SoftDeletes;

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

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'separator' => '-',
                'onUpdate' => true,
            ],
        ];
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'available_levels')
            ->withTimestamps();
    }

    public function institutes(): BelongsToMany
    {
        return $this->belongsToMany(Institute::class, 'institute_levels')
            ->withPivot('institute_custom_level_price')
            ->withPivot('institute_custom_rigth_exam_price')
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

    public function levelCountries(): HasMany
    {
        return $this->hasMany(LevelCountry::class);
    }

    public function getPriceDiscoutedBasedOnRegion(Country $country): float
    {
        return $this->levelCountries->where('country_id', $country)->first()->price_discounted;
    }
}
