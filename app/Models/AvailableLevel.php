<?php

namespace App\Models;

use App\Models\Exam;
use App\Models\Level;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AvailableLevel extends Pivot
{
    protected $fillable = [
        'exam_id',
        'level_id',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }
}
