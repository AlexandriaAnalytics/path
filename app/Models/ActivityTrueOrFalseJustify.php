<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityTrueOrFalseJustify extends Model
{
    use HasFactory;
    protected $table = 'activity_true_or_false_justifies';
    protected $fillable = [
        'question',
        'true',
        'false',
        // 'justify',
        'training_id',
    ];

    public function training(){
        return $this->belongsTo(Training::class);
    }
}
