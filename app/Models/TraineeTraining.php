<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TraineeTraining extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type_of_training_id',
        'trainee_id',
    ];

    public function trainee()
    {
        return $this->belongsTo(Trainee::class);
    }

    public function typeOfTraining()
    {
        return $this->belongsTo(TypeOfTraining::class);
    }
}
