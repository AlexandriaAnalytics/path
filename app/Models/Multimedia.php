<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Multimedia extends Model
{
    use HasFactory;
    protected $fillable = [
        'trining_id',
        'file'
    ];

    public function training(){
        return $this->belongsTo(Training::class);
    }
}
