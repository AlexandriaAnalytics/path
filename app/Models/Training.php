<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Training extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'training';

    protected $fillable = [
        'name',
        'description',
        'section_id',
        'question_type',
        'activity_type'
    ];

    public function section(){
        return $this->belongsTo(Section::class, 'section_id');
    }

    // public function activityTrueOrFalse(){
    //     return $this->hasOne(ActivityTrueOrFalse::class);
    // }

    public function activityTrueOrFalseJustify(){
        return $this->hasOne(ActivityTrueOrFalseJustify::class);
    }
}
