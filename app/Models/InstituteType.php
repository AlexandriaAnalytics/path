<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstituteType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'files_url',
    ];

    protected $attributes = [
        'slug' => '',
    ];

    public function institutes()
    {
        return $this->hasMany(Institute::class);
    }
}
