<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Level extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'slug',
        'modules',
        'tier',
        'complete_price'
    ];

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'available_levels')
            ->withTimestamps();
    }
}
