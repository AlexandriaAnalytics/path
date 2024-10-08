<?php

namespace App\Models;

use App\Services\CandidateService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomLevelPrice extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'custom_level_price';

    protected $fillable = [
        'institute_id',
        'level_country_id',
        'type',
        'full_exam_fee',
        'full_exam_registration_fee',
        'module_registration_fee',
    ];

    protected $casts = [
        'type' => \App\Enums\CustomPricing::class,
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

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function levelCountry(): BelongsTo
    {
        return $this->belongsTo(LevelCountry::class);
    }

    public function customModulePrices(): HasMany
    {
        return $this->hasMany(CustomModulePrice::class);
    }
}
