<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PaymentMethod extends Model
{
    use HasFactory;
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
}
