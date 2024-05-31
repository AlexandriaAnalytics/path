<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityMultipleChoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'question',
        'training_id',
    ];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function multipleChoiceAnswer()
    {
        return $this->hasMany(MultipleChoiceAnswer::class);
    }
}
