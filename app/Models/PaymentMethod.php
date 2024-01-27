<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [
        'name',
    ];

    public static function boot(): void
    {
        parent::boot();
        static::created(function (PaymentMethod $paymentMethod): void {
            $paymentMethod->name = ucfirst($paymentMethod->name);
            $paymentMethod->save();    
        });
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
