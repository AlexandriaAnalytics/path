<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CandidateActivity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['stages', 'comment_at_the_end', 'section_id'];

    protected $casts = [
        'stages' => 'array',
    ];
}
