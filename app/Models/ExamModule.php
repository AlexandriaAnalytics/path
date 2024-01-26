<?php

namespace App\Models;

use App\Models\Exam;
use App\Models\Module;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ExamModule extends Pivot
{
    protected $fillable = [
        'exam_id',
        'module_id',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
