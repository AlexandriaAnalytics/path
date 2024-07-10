<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class candidateAnswer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['answer_text', 'selected_option', 'candidate_id', 'question', 'question_type'];

    protected $casts = [
        'question' => 'array',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
