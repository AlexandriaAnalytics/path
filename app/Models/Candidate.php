<?php

namespace App\Models;

use App\Casts\StudentModules;
use App\Enums\UserStatus;
use Carbon\Carbon;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
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
class Candidate extends Model
{
    protected $table = 'candidates';

    protected $fillable = [
        'level_id',
        'student_id',
        'candidate_number',
        'status',
        'type_of_certificate',
        'granted_discount',
        'payment_ticket_link',
    ];

    protected $attributes = [
        'granted_discount' => 0,
        'status' => UserStatus::Unpaid,
    ];

    public function concepts(): HasMany
    {
        return $this->hasMany(Concept::class);
    }

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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'candidate_id');
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
                return $this->concepts()->sum('amount');
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

    public function getInstalmentCounterAttribute(): string
    {
        if (count($this->payments) == 0) return "Payment not registered";

        if ($this->payments->last()->instalment_number == null) return '1/1';
        else {
            $lastInstalment =  $this->payments()->where('instalment_number', '!=', 'null')->where('status', '!=', 'paid')->orderBy('current_instalment', 'asc')->first()->current_instalment;
            return $lastInstalment . '/' . $this->payments()->where('instalment_number', '!=', 'null')->first()->instalment_number;
        }
    }

    public function getInstalmentAmountAndTotalAttribute()
    {
        $totalAmount = $this->total_amount;
        $incrementalAmount = $this->payments()->where('status', 'paid')->sum('amount');
        return $this->currency . '$ ' . $incrementalAmount . ' / ' . $totalAmount;
    }

    public function getPaymentTypeAttribute()
    {
        if (count($this->payments) == 0) return 'no payments register';
        if (count($this->payments->where('instalment_number', null)->get()->toArray()) != 0) return 'payment totaly';
        if (count($this->payments->where('instalment_number', '!=',  null)->get()->toArray()) != 0) return 'payment financiated';
    }

    public function getPaymentCurrentInstallmentAttribute()
    {
        return $this->payments->where('instalment_number', '!=', null)->where('state', '!=', 'paid')->orderBy('current_instalment', 'asc')->first();
    }

    public function getHasExamSessionsAttribute() {
        return count($this->examSessions) != 0; 
    }

    public function tag(): Attribute
    {
        return new Attribute(
            get: fn () =>  $this->id . '-' . $this->student->name . ' ' . $this->student->surname
        );
    }

    public function getExamSessionsAttribute()
    {
        return $this->candidateExam()->get()->map(fn ($ce) => $ce->exam);
    }

    public function getInstallmentsAvailableAttribute()
    {
        $maxDate = $this->exam_sessions->sortBy('scheduled_date', 0)->first()->scheduled_date ?? null;
        if ($maxDate == null) return -1;
        
        $monthDiff = Carbon::now()->diffInMonths($maxDate,false);
        if ($monthDiff < 0) return -2;

        return $monthDiff + 1;
    }

    public function financing(){
        return $this->hasOne(Financing::class);
    }


}
