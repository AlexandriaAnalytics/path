<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['question', 'description', 'question_type', 'activity_id', 'multimedia', 'title', 'evaluation', 'url', 'question_ids', 'text', 'text_after_answer'];

    protected $casts = [
        'evaluation' => 'boolean',
        'question_type' => 'array',
        'question_ids' => 'array'
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function trueOrFalses()
    {
        return $this->hasMany(TrueFalse::class);
    }

    public function multipleChoices()
    {
        return $this->hasMany(MultipleChoice::class);
    }

    public function openTexts()
    {
        return $this->hasMany(OpenText::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
