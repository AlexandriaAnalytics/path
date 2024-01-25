<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $attributes = [
        'modules' => '[]',
    ];

    protected $fillable = [
        'session_name',
        'scheduled_date',
        'type',
        'maximum_number_of_students',
        'comments',
        'modules',
    ];

    protected $casts = [
        'modules' => \App\Casts\ExamModules::class,
        'scheduled_date' => 'datetime',
        'type' => \App\Enums\ExamType::class,
    ];

    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class)
            ->withTimestamps();
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class)
            ->withTimestamps();
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'candidates')
            ->using(Candidate::class)
            ->withPivot(['id', 'modules'])
            ->withTimestamps();
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }
}
