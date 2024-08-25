<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConceptOtherPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'concept_payment_id', 'other_payment_id'];

    public function conceptPayment()
    {
        return $this->belongsTo(ConceptPayment::class);
    }

    public function otherPayment()
    {
        return $this->belongsTo(OtherPayment::class);
    }
}
