<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Country extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [
        'name',
        'slug',
        'monetary_unit',
        'monetary_unit_symbol',
    ];
    
    /*
    public static function boot(): void
    {
        parent::boot();
        static::created(function (Country $country): void {
            $country->name = ucfirst($country->name);
            $country->monetary_unit = strtoupper($country->monetary_unit);    
            $country->save();
        });
    }

    */

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

    // get modules with price for a country
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'country_module')
            ->withPivot('price');
    }

    public function countryModules()
    {
        return $this->hasMany(CountryModule::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->monetary_unit . $this->monetary_unit_symbol. number_format($this->pivot->price, 2, ',', '.');
    }

    public function getMonetaryPrefixAttribute(): string
    {
        return $this->monetary_unit . $this->monetary_unit_symbol;
    }


}
