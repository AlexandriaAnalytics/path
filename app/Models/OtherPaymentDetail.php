<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtherPaymentDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'other_payment_id', 'amount', 'description', 'link_to_ticket', 'user_id', 'status', 'validated_at', 'comments'];

    public function otherPayment()
    {
        return $this->belongsTo(OtherPayment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
