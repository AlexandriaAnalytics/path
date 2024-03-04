<?php

namespace App\Models;

use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'system_notifications';

    protected $fillable = [
        'institute_type_id',
        'title',
        'body',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (Notification $notification) {
            $users = User::query()
                ->whereHas('institutes', fn (Builder $query) => $query->where('institute_type_id', $notification->institute_type_id))
                ->get();

            FilamentNotification::make()
                ->title($notification->title)
                ->body($notification->body)
                ->sendToDatabase($users);
        });
    }

    public function instituteType(): BelongsTo
    {
        return $this->belongsTo(InstituteType::class);
    }
}
