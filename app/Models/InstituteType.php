<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

use function PHPSTORM_META\map;

class InstituteType extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [
        'name',
    ];

    public function institutes()
    {
        return $this->hasMany(Institute::class);
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'onUpdate' => true,
                'separator' => '_',
            ]
        ];
    }

}
