<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;

/**
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Level> $levels
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Module> $modules
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Student> $students
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Evaluation> $evaluations
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Candidate> $candidates
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Country> $CountryExams
 * @property int $id
 * @property string $session_name
 * @property \Illuminate\Support\Carbon $scheduled_date
 * @property string $type
 * @property int $maximum_number_of_students
 * @property string $comments
 * @property bool $is_able_to_price_pack
 * 
 */
class Exam extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'session_name',
        'scheduled_date',
        'type',
        'maximum_number_of_students',
        'comments',
        'payment_deadline',
        'location'
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'type' => \App\Enums\ExamType::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    public function getAvailableCandidatesAttribute(): int
    {
        return $this->maximum_number_of_students - $this->candidates()->count();
    }

    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class)
            ->withTimestamps();
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'exam_module')
            ->withTimestamps();
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'candidates')
            ->using(Candidate::class)
            ->withPivot(['id'])
            ->withTimestamps();
    }

    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(Candidate::class, 'candidate_exam', 'exam_id', 'candidate_id')
            ->withPivot('module_id')
            ->withTimestamps();
    }

    public function getIsAbleToPricePackAttribute(): bool
    {
        // TODO: Complete this method
        return false;
    }
}
