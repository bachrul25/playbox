<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Rental extends Model
{
    public const TYPE_PRIBADI = 'pribadi';

    public const TYPE_KERJASAMA = 'kerjasama';

    protected $fillable = [
        'invoice_number',
        'playbox_id',
        'partner_id',
        'user_id',
        'rental_type',
        'rental_date',
        'start_time',
        'end_time',
        'duration',
        'price_per_hour',
        'total_income',
        'payment_method',
        'payment_status',
        'customer_name',
        'note',
    ];

    protected $casts = [
        'rental_date' => 'date',
        'duration' => 'decimal:2',
        'price_per_hour' => 'decimal:2',
        'total_income' => 'decimal:2',
    ];

    public function playbox(): BelongsTo
    {
        return $this->belongsTo(Playbox::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function privateReport(): HasOne
    {
        return $this->hasOne(PrivateReport::class);
    }

    public function partnershipReport(): HasOne
    {
        return $this->hasOne(PartnershipReport::class);
    }
}
