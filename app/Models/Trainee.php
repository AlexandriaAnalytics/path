<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trainee extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['full_name', 'phone', 'email', 'types_of_training', 'street_name', 'street_number', 'city', 'postcode', 'province_or_state', 'country_id', 'sections', 'files', 'status'];

    protected $casts = [
        'sections' => 'array',
        'types_of_training' => 'array'
    ];

    public function typeOfTraining()
    {
        return $this->belongsTo(TypeOfTraining::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
