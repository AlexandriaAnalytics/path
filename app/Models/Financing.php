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
        'exam_amount',
        'exam_rigth',
        'state'
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
            get: fn () => $this->payments()
                ->orderBy('current_instalment', 'ASC')
                ->first()
        );
    }

    public function getCurrentInstalmentAttribute()
    {
        return
            $this->payments()->where('status', '!=', 'approved')->orderBy('current_instalment','ASC')->first()->current_instalment;
    }

    public function getTotalInstallmentsAttribute() {
        return $this->payments->count();
    }


    public function getTotalPaymentsPayAttribute()
    {
        return $this->payments()->where('status', 'approved')->sum('amount') ?? 0;
    }

    public function totalUnPaid(): Attribute
    {
        return new Attribute(
            get: fn () => $this->payments()->where('status', '!=', UserStatus::Paid->value)->sum('amount') ?? 0
        );
    }

    public function getTotalAmountAttribute() {
        return $this->institute->candidates->count() >= 0? $this->exam_amount : $this->exam_amount + $this->exam_rigth;
    }

    public function getCurrentPaidAttribute()
    {
        return $this->candidate->payments()->first()->amount ?? 0;
    }

    public function getIsExpiredAttribute()
    {
        return $this->student->payment_current_istallment->current_period;
    }

    public function getCountPaidInstallmentsAttribute(){
        return $this->payments->where('status', '==', 'approved')->count();
    }

    
}
