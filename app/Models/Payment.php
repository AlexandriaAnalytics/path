<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_method',
        'payment_id',
        'currency',
        'amount',
        'status',
        'suscription_code',
        'instalment_number',
        'current_instalment',
        'candidate_id',
        'payment_type',
        'financing_id',
    ];

    protected $attributes = [
        'status' => 'pending'
    ];

    protected function counter(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => ($attributes['instalment_number'] == null ? 'complete' : 'partial')
        );
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function financing(): BelongsTo
    {
        return $this->belongsTo(Financing::class);
    }

}
