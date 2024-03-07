<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'payment_id',
        'payment_method',
        'currency',
        'amount',
        'status',
        'suscription_code',
        'instalment_number',
        'current_instalment',
        'payment_type', //TODO: eliminar campo
        'financing_id',
        'current_period',
        'paid_date',
        'institute_id',
        'link_to_ticket',
        'description',
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

    public function financing(): BelongsTo
    {
        return $this->belongsTo(Financing::class);
    }

    public function getIsExpiredAttribute()
    {
        $currentPeriod = $this->current_period;
        $expiredDate = $this->expiration_date;
      return Carbon::createFromDate($currentPeriod)->diff(Carbon::createFromDate($expiredDate), 'month',true);
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function getTypeAttribute(){
        if($this->instalment_number != null && $this->financing_id == null)
            return 'Suscription';
        else if( $this->instalment_number != null && $this->financing_id != null)
            return 'financing';
        else if($this->instalment_number == null && $this->financing_id == null)
            return 'Simple payment';
    }
}
