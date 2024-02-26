<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property \App\Models\Institute $institute
 * @property \App\Models\Country $region
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Exam> $exams
 * @property int $id
 */
class Student extends Authenticatable
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'surname',
        'email',
        'institute_id',
        'cbu',
        'birth_date',
        'status',
        'personal_educational_needs',
        'country_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

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
        return "{$this->first_name} {$this->surname}";
    }

    public function getAgeAttribute(): int
    {
        return Carbon::now()->diffInYears($this->birth_date);
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

    public function PENs(): string
    {
        if (is_null($this->personal_educational_needs)) {
            return 'null';
        } else {
            return $this->personal_educational_needs;
        }
    }
}
