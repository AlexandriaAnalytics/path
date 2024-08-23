<?php

namespace App\Models;

use App\Enums\UserStatus;
use App\Observers\CandidateObserver;
use App\Services\CandidateService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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

#[ObservedBy([CandidateObserver::class])]
class Candidate extends Model
{
    use SoftDeletes;

    protected $table = 'candidates';

    protected $fillable = [
        'level_id',
        'student_id',
        'candidate_number',
        'status',
        'type_of_certificate',
        'granted_discount',
        'payment_ticket_link',
        'archive'
    ];

    protected $casts = [
        'archive' => 'boolean',
    ];

    protected $attributes = [
        'granted_discount' => 0,
        'installments' => 0,
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
                fn(Builder $query) => $query->whereDoesntHave(
                    'exams',
                    fn(Builder $query) => $query->whereHas(
                        'candidates',
                        fn(Builder $query) => $query->where('candidates.id', $this->id)
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
                return $this->concepts()->count() == 0
                    ? CandidateService::createConcepts($this)->concepts()->sum('amount')
                    : $this->concepts()->sum('amount');
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

    public function getInstallmentCounterAttribute(): string
    {
        if (count($this->payments) == 0) return "Payment not registered";

        if ($this->payments->last()->installment_number == null || !isset($this->payments->last()->installment_numbe)) return '1/1';

        else return (string)$this->financing->current_installment . '/' . (string)$this->financing->payments->count();
    }

    public function getInstallmentAmountAndTotalAttribute()
    {
        $totalAmount = $this->total_amount;
        $incrementalAmount = $this->payments()->where('status', 'paid')->sum('amount');
        return $this->currency . '$ ' . $incrementalAmount . ' / ' . $totalAmount;
    }

    public function getPaymentTypeAttribute()
    {
        if (count($this->payments) == 0) return 'no payments register';
        if (count($this->payments->where('installment_number', null)->get()->toArray()) != 0) return 'payment totaly';
        if (count($this->payments->where('installment_number', '!=',  null)->get()->toArray()) != 0) return 'payment financiated';
    }

    public function getPaymentCurrentInstallmentAttribute()
    {
        return $this
            ->payments->where('installment_number', '!=', null)
            ->where('state', '!=', 'paid')
            ->orderBy('current_installment', 'asc')->first();
    }

    public function getHasExamSessionsAttribute()
    {
        return count($this->examSessions) != 0;
    }

    public function tag(): Attribute
    {
        return new Attribute(
            get: fn() =>  $this->id . '-' . $this->student->name . ' ' . $this->student->surname
        );
    }

    public function getExamSessionsAttribute()
    {
        return $this->candidateExam()->get()->map(fn($ce) => $ce->exam);
    }

    public function getInstallmentsAvailableAttribute()
    {
        $maxDate = $this->exam_sessions->sortBy('scheduled_date', 0)->first()->scheduled_date ?? null;
        if ($maxDate == null) return -1;

        $monthDiff = Carbon::now()->diffInMonths($maxDate, false);
        if ($monthDiff < 0) return -2;

        return $monthDiff + 1;
    }

    public function financings()
    {
        return $this->hasMany(Financing::class);
    }

    public function getCurrentInstallmentAttribute()
    {
        $financing = $this->financings->first();
        if ($financing == null || $financing->current_installment == null) return 'No installments';
        return "{$financing->current_installment}/{$financing->totalInstallments}";
    }

    public function paymentStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                $installments = $this->installmentAttribute;
                $installmentsPaid = Payment::query()->where('candidate_id', $this->id)->where('status', 'approved')->count();
                $status = $this->status;
                if ($status == 'cancelled') {
                    return $status;
                }
                if ($installments - $installmentsPaid == 0) {
                    $status = 'paid';
                }
                if ($installments - $installmentsPaid > 0 && $installmentsPaid > 0) {
                    $status = 'paying';
                }
                if ($installmentsPaid == 0) {
                    $status = 'unpaid';
                }
                if ($this->granted_discount > 0) {
                    $status = 'paying';
                }
                if ($this->granted_discount == 100) {
                    $status = 'paid';
                }
                if ($this->status == 'paid') {
                    $status = 'paid';
                }
                if (Payment::query()->where('candidate_id', $this->id)->where('status', 'approved')->whereNull('installment_number')->where('payment_method', 'mercado_pago')->sum('amount') == Candidate::find($this->id)->totalAmount) {
                    $candidate = Candidate::find($this->id);
                    $status = 'paid';
                    $candidate->installments = 1;
                    $candidate->save();
                }
                $this->status = $status;
                $this->saveQuietly();
                return $status;
            },
        );
    }

    public function installmentAttribute(): Attribute
    {
        return Attribute::make(
            get: function () {
                $installments = $this->installments;
                if ($this->exams != '[]') {
                    $payment_deadline = $this
                        ->exams
                        ->min('payment_deadline');
                    if ($this->status == 'paying') {
                        $firstPayment = Payment::where('candidate_id', $this->id)->where('status', 'approved')->first()->created_at;
                        $firstPaymentDate = Carbon::parse($firstPayment);
                        $paymentDeadlineDate = Carbon::parse($payment_deadline);

                        $firstPaymentDate->day($paymentDeadlineDate->day);


                        $installments = round($firstPaymentDate->diffInMonths($paymentDeadlineDate) + 1);
                    }
                    if (round(
                        now()->diffInMonths(Carbon::parse($payment_deadline), absolute: false),
                        0,
                    ) + 1 < $this->installments) {
                        if ($this->status == 'unpaid') {
                            $installments = round(
                                now()->diffInMonths(Carbon::parse($payment_deadline), absolute: false),
                                0,
                            ) + 1;
                        }
                    }
                }
                if (!$this->student->institute->installment_plans) {
                    $installments = 1;
                    $candidate = Candidate::find($this->id);
                    $candidate->installments = 1;
                    $candidate->save();
                }
                $this->installments = $installments;
                $this->saveQuietly();

                return $installments;
            }
        );
    }

    public function examCost(): Attribute
    {
        return Attribute::make(
            get: function () {
                $amount = 0;
                foreach (Concept::where('candidate_id', $this->id)->where('type', 'exam')->get() as $concept) {
                    $amount = $amount + $concept->amount;
                }
                return $amount;
            }
        );
    }

    public function registrationFee(): Attribute
    {
        return Attribute::make(
            get: function () {
                $amount = 0;
                foreach (Concept::where('candidate_id', $this->id)->where('type', 'registration_fee')->get() as $concept) {
                    $amount = $amount + $concept->amount;
                }
                return $amount;
            }
        );
    }


    public function pendingPayment(): Attribute
    {
        return Attribute::make(
            get: function () {
                $amount = $this->totalAmount - Payment::where('candidate_id', $this->id)->where('status', 'approved')->sum('amount');
                return $amount;
            }
        );
    }

    public function pendingInstallments(): Attribute
    {
        return Attribute::make(
            get: function () {
                $installments = 0;
                $installments = $this->installmentAttribute - Payment::where('candidate_id', $this->id)->where('status', 'approved')->count();
                return $installments;
            }
        );
    }

    public function installment1(): Attribute
    {
        return Attribute::make(
            get: function () {
                $amount = 0;
                if ($this->installments >= 1) {
                    $amount = $this->total_amount / $this->installments;
                }
                return $amount;
            }
        );
    }

    public function installment2(): Attribute
    {
        return Attribute::make(
            get: function () {
                $amount = 0;
                if ($this->installments >= 2) {
                    $amount = $this->total_amount / $this->installments;
                }
                return $amount;
            }
        );
    }

    public function installment3(): Attribute
    {
        return Attribute::make(
            get: function () {
                $amount = 0;
                if ($this->installments >= 3) {
                    $amount = $this->total_amount / $this->installments;
                }
                return $amount;
            }
        );
    }

    public function installment4(): Attribute
    {
        return Attribute::make(
            get: function () {
                $amount = 0;
                if ($this->installments >= 4) {
                    $amount = $this->total_amount / $this->installments;
                }
                return $amount;
            }
        );
    }

    public function installment5(): Attribute
    {
        return Attribute::make(
            get: function () {
                $amount = 0;
                if ($this->installments >= 5) {
                    $amount = $this->total_amount / $this->installments;
                }
                return $amount;
            }
        );
    }

    public function installment6(): Attribute
    {
        return Attribute::make(
            get: function () {
                $amount = 0;
                if ($this->installments >= 6) {
                    $amount = $this->total_amount / $this->installments;
                }
                return $amount;
            }
        );
    }

    public function installment7(): Attribute
    {
        return Attribute::make(
            get: function () {
                $amount = 0;
                if ($this->installments >= 7) {
                    $amount = $this->total_amount / $this->installments;
                }
                return $amount;
            }
        );
    }

    public function installment8(): Attribute
    {
        return Attribute::make(
            get: function () {
                $amount = 0;
                if ($this->installments >= 8) {
                    $amount = $this->total_amount / $this->installments;
                }
                return $amount;
            }
        );
    }

    public function installment9(): Attribute
    {
        return Attribute::make(
            get: function () {
                $amount = 0;
                if ($this->installments >= 9) {
                    $amount = $this->total_amount / $this->installments;
                }
                return $amount;
            }
        );
    }

    public function installment10(): Attribute
    {
        return Attribute::make(
            get: function () {
                $amount = 0;
                if ($this->installments >= 10) {
                    $amount = $this->total_amount / $this->installments;
                }
                return $amount;
            }
        );
    }

    public function installment11(): Attribute
    {
        return Attribute::make(
            get: function () {
                $amount = 0;
                if ($this->installments >= 11) {
                    $amount = $this->total_amount / $this->installments;
                }
                return $amount;
            }
        );
    }

    public function installment12(): Attribute
    {
        return Attribute::make(
            get: function () {
                $amount = 0;
                if ($this->installments >= 12) {
                    $amount = $this->total_amount / $this->installments;
                }
                return $amount;
            }
        );
    }
}
