<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MultipleChoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['answers', 'correct', 'comments', 'question', 'correct_in_pdf'];

    protected $casts = [
        'answers' => 'array',
        'correct' => 'array',
        'comments' => 'array',
        'correct_in_pdf' => 'array'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
