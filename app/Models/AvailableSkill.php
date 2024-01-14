<?php

namespace App\Models;

use App\Models\Exam;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Model;

class AvailableSkill extends Model
{
    /**
     * * User primary key.
     * @var string
     */
    protected $primaryKey = 'id_available_skill';

    /**
     * * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'id_exam',
        'id_skill',
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
    public function skill()
    {
        return $this->belongsTo(Skill::class, 'id_skill', 'id_skill');
    }
}
