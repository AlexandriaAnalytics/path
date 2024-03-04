<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomLevelPrice extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'institute_custom_level_price';

    protected $fillable = [
        'institute_id',
        'level_country_id',
        'type',
        'exam_registration_fee',
        'module_registration_fee',
    ];

    protected $type = [
        'type' => \App\Enums\CustomPricing::class,
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function levelCountry(): BelongsTo
    {
        return $this->belongsTo(LevelCountry::class);
    }
}
