<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Examiner extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'surname',
        'address',
        'phone',
        'email',
        'password',
    ];

    protected $appends = [
        'name_surname'
    ];

    public function getNameSurnameAttribute()
    {
        return "$this->name + $this->surname";
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'examiner';
    }

    public function getFilamentName(): string
    {
        return "{$this->name} {$this->surname}";
    }
}
