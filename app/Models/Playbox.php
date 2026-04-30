<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Playbox extends Model
{
    protected $table = 'playboxes';

    public const STATUS_TERSEDIA = 'tersedia';

    public const STATUS_DISEWA = 'disewa';

    public const STATUS_MAINTENANCE = 'maintenance';

    public const STATUS_TIDAK_AKTIF = 'tidak_aktif';

    public const OWNERSHIP_PRIBADI = 'pribadi';

    public const OWNERSHIP_KERJASAMA = 'kerjasama';

    protected $fillable = [
        'code',
        'name',
        'ownership_type',
        'partner_id',
        'location',
        'status',
        'default_price_per_hour',
        'condition_note',
    ];

    protected $casts = [
        'default_price_per_hour' => 'decimal:2',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
