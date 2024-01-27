<?php

namespace App\Models;

use App\Models\Candidate;
use App\Models\Module;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CandidateModule extends Pivot
{
    protected $fillable = [
        'candidate_id',
        'module_id',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
