<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityTrueOrFalse extends Model
{
    use HasFactory;

    protected $table = 'activity_true_or_false';

    protected $fillable = [
        'question',
        'true',
        'false',
        'answer_check',
        'training_id'
    ];

    public function training(){
        return $this->belongsTo(Training::class, 'training_id');
    }
}
