<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrueFalse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['true', 'comments', 'question_id'];

    protected $casts = ['true' => 'boolean', 'comments' => 'array'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
