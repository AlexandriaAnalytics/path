<?php

namespace App\Models;

use App\Services\CandidateService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomModulePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_level_price_id',
        'module_id',
        'price',
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

    public function customLevelPrice(): BelongsTo
    {
        return $this->belongsTo(CustomLevelPrice::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
