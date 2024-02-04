<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'country_module')
            ->withPivot('price')
            ->withTimestamps();
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

    public function CandidateExams(): HasMany
    {
        return $this->hasMany(CandidateExam::class);
    }

    public function countryModules()
    {
        return $this->hasMany(CountryModule::class);
    }
}
