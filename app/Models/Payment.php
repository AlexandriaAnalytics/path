<?php

namespace App\Models;

use App\Enums\PaymentType;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'candidate_id',
        'payment_id',
        'payment_method',
        'currency',
        'amount',
        'status',
        'suscription_code',
        'installment_number',
        'current_installment',
        'payment_type', //TODO: eliminar campo
        'financing_id',
        'current_period',
        'paid_date',
        'institute_id',
        'link_to_ticket',
        'description',
        'pay_id',
    ];


    protected $attributes = [
        'status' => 'pending',
    ];

    protected function counter(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => ($attributes['installment_number'] == null ? 'complete' : 'partial')
        );
    }

    public function financing(): BelongsTo
    {
        return $this->belongsTo(Financing::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'pay_id');
    }

    public function parent()
    {
        return $this->belongsTo(Payment::class, 'pay_id');
    }

    public function getIsExpiredAttribute()
    {
        $currentPeriod = $this->current_period;
        $expiredDate = $this->expiration_date;
        return Carbon::createFromDate($currentPeriod)->diff(Carbon::createFromDate($expiredDate), 'month', true);
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSubscriptions(Builder $query)
    {
        return $query->whereNotNull('installment_number')
            ->whereNull('financing_id');
    }

    public function scopeFinancings(Builder $query)
    {
        return $query->whereNotNull('installment_number')
            ->whereNotNull('financing_id');
    }

    public function scopeSimplePayments(Builder $query)
    {
        return $query->whereNull('installment_number')
            ->whereNull('financing_id');
    }

    public function getTypeAttribute()
    {
        if ($this->installment_number != null && $this->financing_id == null)
            return PaymentType::Subscription;
        else if ($this->installment_number != null && $this->financing_id != null)
            return PaymentType::Financing;
        else if ($this->installment_number == null && $this->financing_id == null)
            return PaymentType::SimplePayment;

        throw new Exception('Payment type not found');
    }
}
