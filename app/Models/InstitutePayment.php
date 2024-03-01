<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'ticket_link',
        'monthly_amount',
        'description',
    ];
}
