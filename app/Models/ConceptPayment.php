<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConceptPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'description'];

    public function otherPayments()
    {
        return $this->belongsToMany(OtherPayment::class, 'concept_other_payments');
    }
}
