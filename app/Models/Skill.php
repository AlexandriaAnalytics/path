<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'skill_name',
    ];

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'available_levels');
    }
}
