<?php

namespace App\Models;

use App\Models\ExamSession;
use App\Models\Candidate;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CandidateExam extends Pivot
{
    protected $fillable = [
        'examsession_id',
        'candidate_id',
    ];

    public function examSession()
    {
        return $this->belongsTo(ExamSession::class);
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
