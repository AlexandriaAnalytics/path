<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionAnswer extends Model
{
    use HasFactory;
    protected $fillable = [
        'question',
        'answer',
        'training_id'
    ];

    public function training(){
        return $this->belongsTo(Training::class);
    }
}
