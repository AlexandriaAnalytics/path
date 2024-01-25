<?php

namespace App\Models;

use App\Models\Student;
use App\Models\Module;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ModuleStudent extends Pivot
{
    protected $fillable = [
        'student_id',
        'module_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
