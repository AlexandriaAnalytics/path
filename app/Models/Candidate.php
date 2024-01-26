<?php

namespace App\Models;

use App\Casts\StudentModules;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Candidate extends Pivot
{
    public $incrementing = true;

    protected $table = 'candidates';

    protected $fillable = [
        'exam_id',
        'student_id',
        'modules',
        'status',
    ];

    protected $casts = [
        'modules' => StudentModules::class,
        'status' => UserStatus::class,
    ];

    protected $attributes = [
        'modules' => '[]',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }
}
