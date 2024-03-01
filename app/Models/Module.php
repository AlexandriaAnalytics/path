<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Module extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'exam_module')
            ->withTimestamps();
    }

    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(Candidate::class, 'candidate_module', 'candidate_id', 'module_id')
            ->withTimestamps()
            ->withPivot('status');
    }

    public function candidateExams(): HasMany
    {
        return $this->hasMany(CandidateExam::class);
    }

    public function levelCountries(): BelongsToMany
    {
        return $this->belongsToMany(LevelCountry::class, 'level_country_module')
            ->withPivot('price')
            ->withTimestamps();
    }

    public function levelCountryModules(): HasMany
    {
        return $this->hasMany(LevelCountryModule::class);
    }
}
