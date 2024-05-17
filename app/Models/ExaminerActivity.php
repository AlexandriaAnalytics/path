<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExaminerActivity extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['questions', 'section_id'];

    protected $casts = [
        'questions' => 'array',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function preguntas()
    {
        $questions = $this->questions;
        $questionsArray = [];
        foreach ($questions as $question) {
            $questionsArray[] = ExaminerQuestion::find($question)->first();
        }
        return $questionsArray;
    }
}
