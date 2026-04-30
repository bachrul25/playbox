<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnershipReport extends Model
{
    protected $fillable = [
        'rental_id',
        'partner_id',
        'total_income',
        'staff_cost',
        'net_income',
        'owner_share',
        'partner_share',
        'share_percentage',
        'report_date',
    ];

    protected $casts = [
        'report_date' => 'date',
        'total_income' => 'decimal:2',
        'staff_cost' => 'decimal:2',
        'net_income' => 'decimal:2',
        'owner_share' => 'decimal:2',
        'partner_share' => 'decimal:2',
        'share_percentage' => 'decimal:2',
    ];

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
