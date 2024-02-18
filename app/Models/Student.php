<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \App\Models\Institute $institute
 * @property \App\Models\Country $region
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Exam> $exams
 * @property int $id
 */
class Student extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'institute_id',
        'names',
        'surnames',
        'country',
        'cbu',
        'birth_date',
        'status',
        'personal_educational_needs'
    ];

    protected $casts = [
        'country' => \App\Enums\Country::class,
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    public function firstName(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => ucwords($value),
        );
    }

    public function lastName(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => ucwords($value),
        );
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->surnames}";
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'candidates')
            ->using(Candidate::class)
            ->withPivot(['id'])
            ->withTimestamps();
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
