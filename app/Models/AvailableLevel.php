<?php

namespace App\Models;

use App\Models\Exam;
use App\Models\Level;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AvailableLevel extends Pivot
{
    /**
     * * User primary key.
     * @var string
     */
    protected $primaryKey = 'id_available_level';

    /**
     * * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'id_exam',
        'id_level',
    ];

    /**
     * * Get the Exam that owns the AvailableLevel
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class, 'id_exam', 'id_exam');
    }

    /**
     * * Get the Level that owns the AvailableLevel
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function level()
    {
        return $this->belongsTo(Level::class, 'id_level', 'id_level');
    }
}
