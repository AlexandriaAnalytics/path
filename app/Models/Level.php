<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Exam> $exams
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Institute> $institutes
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Country> $countries
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\LevelCountry> $levelCountries
 * @property int $id
 * @property string $name
 * @property float $price
 * @property string $slug
 * @property int $minimum_age
 * @property int $maximum_age
 * @property string $modules
 * @property string $tier
 * @property float $complete_price
 */
class Level extends Model
{
    use HasFactory;
    use LogsActivity;
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

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
            ->withPivot(['price_discounted', 'price_right_exam'])
            ->withTimestamps();
    }

    public function levelCountries()
    {
        return $this->hasMany(LevelCountry::class);
    }
}
