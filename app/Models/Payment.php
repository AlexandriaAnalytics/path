<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_method',
        'payment_id',
        'currency',
        'amount',
        'status',
        'instalment_number',
        'current_instalment',
        'candidate_id',
        'payment_type',
    ];

    protected $attributes = [
        'status' => 'pending'
    ];

    protected static function booted(): void
    {
        static::created(function(Payment $payment) {
            
            if($payment->instalment_number != null) {
                $previusPayments = Payment::where('candidate_id', $payment->candidate_id)->get();
                $lastInstalment = $previusPayments->max('current_instalment');
                
                if(
                    $payment->instalment_number != null && $lastInstalment == $payment->instalment_number
                ){
                    throw new Exception('yo can not pay more');
                }
                
                if(count($previusPayments) == 0){
                    $payment->current_instalment = 1;
                }else {
                    $lastInstalment = $previusPayments->max('current_instalment');
                    $payment->current_instalment = $lastInstalment + 1;
                }
                $payment->save();
            }
            
        });
    }

    protected function counter() : Attribute {
       return Attribute::make(
               get: fn ($value, $attributes) =>
                    ($attributes['instalment_number'] == null? 'complete' : 'partial')
       );
}
}
