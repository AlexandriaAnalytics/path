<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Exam> $exams
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Institute> $institutes
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Country> $countries
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\Module> $modules
 * @property int $id
 * @property string $name
 * @property float $price
 * @property string $slug
 * @property int $minimum_age
 * @property int $maximum_age
 * @property float $complete_price
 */
class Level extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'minimum_age',
        'maximum_age',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class)
            ->withTimestamps();
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'level_country')
            ->withPivot(['price_all_modules', 'price_exam_right_all_modules', 'price_exam_right'])
            ->withTimestamps();
    }

    public function levelCountries(): HasMany
    {
        return $this->hasMany(LevelCountry::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class);
    }

    public function certificateTypes(): BelongsToMany
    {
        return $this->belongsToMany(CertificateType::class, 'level_certificate_type', 'level_id', 'certificate_type_id');
    }

    public function modalities(): BelongsToMany
    {
        return $this->belongsToMany(Modality::class, 'level_modality', 'level_id', 'modality_id');
    }
}
