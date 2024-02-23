<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\PaymentMethod> $paymentMethods
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Module> $modules
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\CountryExam> $countryExams
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Level> $levels
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $monetary_unit
 * @property string $monetary_unit_symbol
 */
class Country extends Model
{
    use HasFactory;
    use LogsActivity;
    use Sluggable;

    protected $fillable = [
        'name',
        'slug',
        'monetary_unit',
        'monetary_unit_symbol',
    ];

    protected $attributes = [
        'monetary_unit' => 'USD',
        'monetary_unit_symbol' => '$',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'separator' => '_',
                'onUpdate' => true,
            ],
        ];
    }

    public function monetaryString(): string
    {
        return $this->monetary_unit_symbol . $this->monetary_unit;
    }

    public function paymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(PaymentMethod::class, 'country_payment_method')->withTimestamps();
    }

    public function countryExams(): HasMany
    {
        return $this->hasMany(CountryExam::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->monetary_unit . $this->monetary_unit_symbol . number_format($this->pivot->price, 2, ',', '.');
    }

    public function getMonetaryPrefixAttribute(): string
    {
        return $this->monetary_unit . $this->monetary_unit_symbol;
    }

    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class, 'level_country')
            ->withPivot([
                'price_all_modules',
                'price_exam_right_all_modules',
                'price_exam_right',
            ])
            ->withTimestamps();
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
