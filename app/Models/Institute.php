<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
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
        'maximum_cumulative_discount',
        'unique_number',
        'can_view_registration_fee',
        'installment_plans',
        'can_add_candidates',
        'mora',
    ];

    protected $attributes = [
        'can_view_registration_fee' => false,
        'can_add_candidates' => true,
        'maximum_cumulative_discount' => 0,
        'discounted_price_diferencial' => 0,
        'discounted_price_percentage' => 0,
        'rigth_exam_diferencial' => 100,
    ];

    public static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(function (Builder $query) {
            $query->addSelect([
                'institutes.*',
                'remaining_discount' => Candidate::query()
                    ->whereHas('student', fn (Builder $query) => $query->whereColumn('institute_id', 'institutes.id'))
                    ->select(DB::raw('maximum_cumulative_discount - coalesce(sum(granted_discount), 0)'))
            ]);
        });

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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
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

    public function customLevelPrices(): HasMany
    {
        return $this->hasMany(CustomLevelPrice::class);
    }

    public function candidates(): HasManyThrough
    {
        return $this->hasManyThrough(Candidate::class, Student::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function getLevelPaymentDiferencial(string $levelName): object
    {
        return $this->instituteLevels->where('level.name', $levelName)->first();
    }
}
