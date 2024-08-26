<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtherPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'names', 'surnames', 'birth_date', 'personal_ID', 'amount_to_be_paid', 'amount_paid', 'currency', 'limit_date', 'link_to_ticket', 'institute_id', 'candidate_id', 'comments', 'archived', 'status'];

    public function conceptPayments()
    {
        return $this->belongsToMany(ConceptPayment::class, 'concept_other_payments');
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
