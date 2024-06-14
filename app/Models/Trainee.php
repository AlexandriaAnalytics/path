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

    protected $fillable = ['phone', 'street_name', 'street_number', 'city', 'postcode', 'province_or_state', 'country_id', 'sections', 'files', 'status', 'user_id', 'cbu', 'alias', 'bank_account_owner', 'bank_account_owner_id'];

    protected $casts = [
        'sections' => 'array',
    ];


    public function typeOfTraining()
    {
        return $this->belongsToMany(TypeOfTraining::class, 'trainee_training', 'trainee_id', 'type_of_training_id')
            ->withTimestamps();
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
