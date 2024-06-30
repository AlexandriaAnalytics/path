<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CandidateRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['candidate_id', 'section_id', 'status_activity_id', 'comments', 'performance_id', 'result', 'type_of_training_id'];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function statusActivity()
    {
        return $this->belongsTo(StatusActivity::class);
    }

    public function performance()
    {
        return $this->belongsTo(Performance::class);
    }

    public function typeOfTraining()
    {
        return $this->belongsTo(TypeOfTraining::class);
    }
}
