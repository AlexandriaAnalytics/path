<?php

namespace App\Models;

use App\Enums\ConceptType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Concept extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'candidate_id',
        'description',
        'type',
        'currency',
        'amount',
    ];

    protected $casts = [
        'type' => ConceptType::class,
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
