<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'institute_id',
        'national_id',
        'first_name',
        'last_name',
        'slug',
        'country',
        'address',
        'phone',
        'cbu',
        'birth_date',
        'status',
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

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'candidates')
            ->using(Candidate::class)
            ->withTimestamps();
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'id_user', 'id_user');
    }
}
