<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Training extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'training';

    protected $fillable = [
        'name',
        'description',
        'section_id',
        'question_type',
        'activity_type'
    ];

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function trainee()
    {
        return $this->belongsToMany(Trainee::class, 'trainee_training', 'trainee_id', 'type_of_training_id')
            ->withTimestamps();
    }

    // public function activityTrueOrFalse(){
    //     return $this->hasOne(ActivityTrueOrFalse::class);
    // }

    public function activityTrueOrFalseJustify()
    {
        return $this->hasOne(ActivityTrueOrFalseJustify::class);
    }

    public function trueOrFalse()
    {
        return $this->hasOne(TrueOrFalse::class);
    }

    public function ativityMultipleChoice()
    {
        return $this->hasOne(ActivityMultipleChoice::class);
    }

    public function questionAnswer()
    {
        return $this->hasOne(QuestionAnswer::class);
    }

    public function multimedia()
    {
        return $this->hasOne(Multimedia::class);
    }
}
