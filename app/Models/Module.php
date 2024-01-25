<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'price'
    ];

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'available_levels')
            ->withTimestamps();
    }
}
