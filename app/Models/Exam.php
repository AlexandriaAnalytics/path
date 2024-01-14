<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'institute_id',
        'exam_session_name',
        'scheduled_date',
        'type',
        'maximum_number_of_candidates',
        'comments'
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function levels()
    {
        return $this->belongsToMany(Level::class, 'available_levels', 'id_exam', 'id_level');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'available_skills', 'id_exam', 'id_skill');
    }
}
