<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'slug',
        'modules',
        'tier',
        'complete_price'
    ];

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'available_levels');
    }
}
