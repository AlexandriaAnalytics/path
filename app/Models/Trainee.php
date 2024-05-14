<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Trainee extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['full_name', 'phone', 'email','password', 'types_of_training', 'street_name', 'street_number', 'city', 'postcode', 'province_or_state', 'country_id', 'sections', 'files', 'status'];

    protected $casts = [
        'sections' => 'array',
        'types_of_training' => 'array',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function typeOfTraining()
    {
        return $this->belongsTo(TypeOfTraining::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if($panel->getId() === 'trainee' && $this->status) return true;
        
    }

    public function getFilamentName(): string
    {
        return "{$this->full_name}";
    }
}
