<?php

namespace App\Models;

use App\Casts\StudentModules;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
 * @property string $currency
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
        'candidate_number',
        'status',
        'type_of_certificate'
    ];

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

    public function exam(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'candidate_exam', 'candidate_id', 'exam_id')
            ->withTimestamps();
    }

    public function totalAmount(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                $amount = 0;

                if (Module::all()->diff($this->modules)->isEmpty()) {
                    // If the student has all the modules, apply the complete price
                    // that may be different from the sum of the individual modules prices
                    $amount = $this
                        ->level
                        ->countries
                        ->firstWhere('id', $this->student->region->id)
                        ->pivot
                        ->price_discounted;
                } else {
                    // If the student does not have all the modules, apply the sum of the individual
                    // modules prices
                    $amount =  $this
                        ->level
                        ->countries
                        ->firstWhere('id', $this->student->region->id)
                        ->modules
                        ->intersect($this->modules)
                        ->sum('pivot.price');
                }

                // If the institute has an additional price for this level, apply it
                $amount += $this
                    ->student
                    ->institute
                    ->levels
                    ->firstWhere('id', $this->level->id)
                    ->pivot
                    ->institute_diferencial_aditional_price;

                return $amount;
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
