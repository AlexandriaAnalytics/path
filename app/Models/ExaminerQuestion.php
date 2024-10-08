<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExaminerQuestion extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['question', 'description', 'aswers', 'open_or_close', 'performance', 'multimedia'];

    protected $casts = [
        'open_or_close' => 'boolean',
        'aswers' => 'array',
        'performance' => 'array',
    ];
}
