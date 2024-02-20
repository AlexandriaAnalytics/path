<?php

namespace App\Models;

use App\Casts\StudentModules;
use App\Enums\UserStatus;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;

/**
 * @property \App\Models\Student $student
 * @property \App\Models\Level $level
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Module> $modules
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Exam> $exam
 * @property int $id
 * @property int $level_id
 * @property int $student_id
 * @property float $total_amount
 * @property string $candidate_number
 * @property string $status
 * @property string $type_of_certificate
 */
class Candidate extends Pivot
{
    public $incrementing = true;

    protected $table = 'candidates';

    protected $fillable = [
        'level_id',
        'student_id',
        'billed_concepts',
        'candidate_number',
        'status',
        'type_of_certificate'
    ];

    protected $casts = [
        'billed_concepts' => AsCollection::class,
    ];

    protected $attributes = [
        'billed_concepts' => '[]',
        'status' => UserStatus::Unpaid,
    ];

    public function candidateExam(): HasMany
    {
        return $this->hasMany(CandidateExam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'candidate_module', 'candidate_id', 'module_id')
            ->withTimestamps();
    }

    /**
     * Get the pending modules to be assigned to the candidate.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function pendingModules(): BelongsToMany
    {
        return $this
            ->modules()
            ->where(
                fn (Builder $query) => $query->whereDoesntHave(
                    'exams',
                    fn (Builder $query) => $query->whereHas(
                        'candidates',
                        fn (Builder $query) => $query->where('candidates.id', $this->id)
                            ->whereColumn('module_id', 'modules.id'),
                    ),
                ),
            );
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'candidate_exam', 'candidate_id', 'exam_id')
            ->withPivot('module_id')
            ->withTimestamps();
    }

    public function totalAmount(): Attribute
    {
        return Attribute::make(
            get: function () {
                // @TODO: Implement this method
                return 75.25;
            },
        );
    }

    public function currency(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->student->region->monetary_unit;
            },
        );
    }

    public function getMonetaryString()
    {
        return $this->student->region->monetary_unit . $this->student->region->monetary_unit_symbol;
    }
}
