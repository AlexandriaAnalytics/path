<?php

namespace App\Models;

use App\Models\Exam;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AvailableSkill extends Pivot
{
    protected $fillable = [
        'exam_id',
        'skill_id',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }
}
