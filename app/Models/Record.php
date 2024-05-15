<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Record extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['trainee_id', 'section_id', 'status_id', 'comments', 'performance_id'];

    public function trainee()
    {
        return $this->belongsTo(Trainee::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function status()
    {
        return $this->belongsTo(StatusActivity::class);
    }

    public function performance()
    {
        return $this->belongsTo(Performance::class);
    }
}
