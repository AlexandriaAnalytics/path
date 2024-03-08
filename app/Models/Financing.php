<?php

namespace App\Models;

use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Financing extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'candidate_id',
        'institute_id',
        'currency',
        'total_amount',
        'exam_rigth',
    ];

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    // App\Models\Financing.php
    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function currentPayment(): Attribute
    {
        return new Attribute(
            get: fn() => $this->payments()
                ->orderBy('current_instalment', 'ASC')
                ->first()
        );
    }

    public function currentInstalment(): Attribute
    {
        return new Attribute(
            get: fn() => $this->payments()->where('status', '!=', UserStatus::Paid->value)
                ->orderBy('current_instalment', 'ASC')
                ->first()->current_instalment ?? ''
        );
    }

    public function getFinalAmountAttribute() {
        $final_amount = 0;
        if($this->institute->count() < 30 ){
            
        }      
    }

/*
    public function totalAmount(): Attribute
    {
        return new Attribute(
            get: fn() => $this->payments()->sum('amount')
        );
    }
*/

    public function totalPaid(): Attribute
    {


        return new Attribute(
            get: function(){
                
               $totalAmount =  $this->payments()->where('status', UserStatus::Paid->value)->sum('amount') ?? 0;
                return $totalAmount;
               
            }
        );
    }

    public function totalUnPaid(): Attribute
    {
        return new Attribute(
            get: fn() => $this->payments()->where('status', '!=', UserStatus::Paid->value)->sum('amount') ?? 0
        );
    }

    public function getCurrentPaidAttribute() {
        return $this->candidate->payments()->first()->amount ?? 0;
    }

    public function getIsExpiredAttribute() {
        return $this->student->payment_current_istallment->current_period;
    }
}
