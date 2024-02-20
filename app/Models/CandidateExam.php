<?php

namespace App\Models;

use App\Models\Exam;
use App\Models\Candidate;
use App\Models\Module;
use Illuminate\Database\Eloquent\Model;

class CandidateExam extends Model
{
    protected $table = 'candidate_exam';

    protected $fillable = [
        'exam_id',
        'candidate_id',
        'module_id'
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
