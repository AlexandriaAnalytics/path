<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Institute extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'files_url',
        'institute_type_id',
        'owner_id',
        'email',
        'phone',
        'street_name',
        'number',
        'city',
        'province',
        'postcode',
        'country',
    ];

    protected $attributes = [
        'can_add_candidates' => true,
    ];

    public static function boot(): void
    {
        parent::boot();

        static::created(function (Institute $institute): void {
            Log::info('Created institute', ['institute' => $institute->toArray()]);
            if ($institute->name == null && isset($institute->owner)) {
                $institute->name = $institute->owner->name . ' s`Institute';
            }
            if ($institute->owner)
                $institute->users()->sync([$institute->owner->id]);
            else
                $institute->users()->detach();
            $institute->save();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
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

    public function instituteType(): BelongsTo
    {
        return $this->belongsTo(InstituteType::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class, 'institute_level')
            ->withPivot('institute_diferencial_percentage_price')
            ->withPivot('institute_diferencial_aditional_price')
            ->withPivot('institute_right_exam')
            ->withPivot('can_edit')
            ->withTimestamps();
    }

    public function instituteLevels(): HasMany
    {
        return $this->hasMany(InstituteLevel::class);
    }

    public function getLevelPaymentDiferencial(string $levelName): object
    {
        return $this->instituteLevels->where('level.name', $levelName)->first();
    }
}
