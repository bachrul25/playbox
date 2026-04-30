<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    public const TYPES = ['maintenance', 'perawatan', 'kerusakan', 'staff', 'lainnya'];

    protected $fillable = [
        'playbox_id',
        'partner_id',
        'expense_date',
        'type',
        'amount',
        'description',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function playbox(): BelongsTo
    {
        return $this->belongsTo(Playbox::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
