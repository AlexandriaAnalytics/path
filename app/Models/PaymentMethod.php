<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PaymentMethod extends Model
{
    use HasFactory, Sluggable;
    use LogsActivity;

    protected $fillable = [
        'name',
        'description'
    ];

    public static function boot(): void
    {
        parent::boot();
        static::created(function (PaymentMethod $paymentMethod): void {
            $paymentMethod->name = ucfirst($paymentMethod->name);
            $paymentMethod->save();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'separator' => '_',
                'onUpdate' => true,
            ],
        ];
    }
}
