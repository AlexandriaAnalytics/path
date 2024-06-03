<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrueOrFalse extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'true',
        'false',
        'training_id'
    ];

    public function training(){
        return $this->belongsTo(Training::class);
    }
}
