<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Answer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['answer_text', 'selected_option', 'trainee_id', 'question_id', 'question_type', 'section_id'];

    public function trainee()
    {
        return $this->belongsTo(Trainee::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
