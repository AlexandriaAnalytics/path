<?php

namespace App\Models;

use App\Services\CandidateService;
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
        'price_all_modules',
        'price_exam_right_all_modules',
        'price_exam_right',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::saved(function () {
            $candidates = Candidate::all();

            foreach ($candidates as $candidate) {
                if ($candidate->paymentStatus == 'unpaid') {
                    $candidate->concepts()->delete();
                    CandidateService::createConcepts($candidate);
                }
            }
        });
    }

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
