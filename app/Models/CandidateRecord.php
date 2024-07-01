<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CandidateRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['candidate_id', 'section_id', 'status_activity_id', 'comments', 'performance_id', 'result', 'type_of_training_id', 'attendance', 'can_access'];

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

    public function attendanceAttribute(): Attribute
    {
        return Attribute::make(
            get: function () {
                $attendance = $this->attendance;
                if ($attendance == '-') {
                    $currentDate = date('Y-m-d H:i:s');
                    $scheduledDate = CandidateExam::where('candidate_id', $this->candidate_id)->first()->exam->scheduled_date->modify('+3 hours');
                    $duration = CandidateExam::where('candidate_id', $this->candidate_id)->first()->exam->duration;
                    if ($currentDate >= $scheduledDate->modify('+' . $duration . ' minutes')) {
                        $attendance = 'Absent';
                    }
                }
                $this->attendance = $attendance;
                $this->saveQuietly();
                return $attendance;
            },
        );
    }
}
