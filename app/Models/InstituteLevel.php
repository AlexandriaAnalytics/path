<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class InstituteLevel extends Pivot
{
   public function institute(): BelongsTo
   {
       return $this->belongsTo(Institute::class);
   }

   public function level(): BelongsTo
   {
       return $this->belongsTo(Level::class);
   }
}
