<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institute extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'files_url',
        'can_add_candidates',
    ];

    protected $casts = [
        'type' => \App\Enums\InstituteType::class,
    ];

    protected $attributes = [
        'can_add_candidates' => true,
    ];

    public static function boot(): void
    {
        parent::boot();

        static::saving(function (Institute $institute): void {
            if ($institute->name == null) {
                $institute->name = $institute->users()->first()?->name;
            }
        });
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
