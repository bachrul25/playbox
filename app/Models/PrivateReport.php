<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivateReport extends Model
{
    protected $fillable = [
        'rental_id',
        'total_income',
        'maintenance_amount',
        'owner_profit',
        'maintenance_percentage',
        'owner_percentage',
        'report_date',
    ];

    protected $casts = [
        'report_date' => 'date',
        'total_income' => 'decimal:2',
        'maintenance_amount' => 'decimal:2',
        'owner_profit' => 'decimal:2',
        'maintenance_percentage' => 'decimal:2',
        'owner_percentage' => 'decimal:2',
    ];

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
}
