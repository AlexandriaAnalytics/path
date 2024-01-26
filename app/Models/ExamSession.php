<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'session_name',
        'scheduled_date'
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
