<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MultipleChoiceAnswer extends Model
{
    use HasFactory;
    protected $table = 'multiple_choice_answers';
    protected $fillable = [
        'answer',
        'check',
        'activity_multiple_choice_id'
    ];
    public function activtyMultipleChoice(){
        return $this->belongsTo(ActivityMultipleChoice::class,'activity_multiple_choice_id');
    }
}
