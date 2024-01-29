<?php

namespace App\Models;

use App\Casts\StudentModules;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;

class Candidate extends Pivot
{
    public $incrementing = true;

    protected $table = 'candidates';

    protected $fillable = [
        'exam_id',
        'student_id',
        'candidate_number',
        'status',
    ];

    protected $casts = [
        'status' => UserStatus::class,
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'candidate_module', 'candidate_id', 'module_id')
            ->withTimestamps();
    }

    public function examsessions(): BelongsToMany
    {
        return $this->belongsToMany(ExamSession::class, 'candidate_exam', 'examsession_id', 'candidate_id')
            ->withTimestamps();
    }
}
