<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['section_id', 'type_of_training_id'];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function typeOfTraining()
    {
        return $this->belongsTo(TypeOfTraining::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
