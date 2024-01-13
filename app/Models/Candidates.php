<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidates extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'slug',
        'id_country',
        'address',
        'phone',
        'cbu',
        'cuil',
        'birth_date',
        'status',
        'id_created_by'
    ]
}
