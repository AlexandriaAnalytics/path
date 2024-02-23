<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'candidate_id'
    ];

    protected $attributes = [
        'status' => 'pending'
    ];
}
