<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Trainee extends Authenticatable
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['phone', 'types_of_training', 'street_name', 'street_number', 'city', 'postcode', 'province_or_state', 'country_id', 'sections', 'files', 'status', 'user_id'];

    protected $casts = [
        'sections' => 'array',
        'types_of_training' => 'array',
    ];


    public function typeOfTraining()
    {
        return $this->belongsTo(TypeOfTraining::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
