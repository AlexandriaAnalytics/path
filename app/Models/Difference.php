<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Difference extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'total_amount', 'paid_amount', 'solved', 'payment_id'];

    protected $casts = [
        'solved' => 'boolean'
    ];
}
