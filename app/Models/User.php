<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin' && !$this->hasRole('Superadministrator')) {
            return false;
        }

        return true;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->institutes->contains($tenant);
    }

    public function getTenants(Panel $panel): array|Collection
    {
        return $this->institutes;
    }

    public function institutes(): BelongsToMany
    {
        return $this->belongsToMany(Institute::class, 'institute_user')
            ->withTimestamps();
    }

    public function institutions(): BelongsToMany
    {
        return $this->belongsToMany(Institute::class, 'institute_user')
            ->withTimestamps();
    }
}
