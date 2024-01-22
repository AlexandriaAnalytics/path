<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_student',
        'id_exam',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'id_exam', 'id_exam');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'id_student', 'id_student');
    }
}
