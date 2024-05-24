<?php

namespace App\Models;

use App\Enums\ModuleType;
use App\Services\CandidateService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

// https://filamentphp.com/docs/3.x/forms/fields/repeater#integrating-with-a-belongstomany-eloquent-relationship
class LevelCountryModule extends Pivot
{
    protected $fillable = [
        'level_country_id',
        'module_id',
        'price',
        'module_type',
    ];

    protected $casts = [
        'module_type' => ModuleType::class,
    ];

    public static function boot(): void
    {
        parent::boot();

        /* static::saved(function () {
            $candidates = Candidate::all();

            foreach ($candidates as $candidate) {
                if ($candidate->paymentStatus == 'unpaid') {
                    $candidate->concepts()->delete();
                    CandidateService::createConcepts($candidate);
                }
            }
        }); */
    }

    public function levelCountry(): BelongsTo
    {
        return $this->belongsTo(LevelCountry::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
