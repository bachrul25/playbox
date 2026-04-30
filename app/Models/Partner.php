<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    protected $fillable = [
        'cafe_name',
        'person_in_charge',
        'phone',
        'address',
        'cooperation_start_date',
        'status',
        'note',
    ];

    protected $casts = [
        'cooperation_start_date' => 'date',
    ];

    public function playboxes(): HasMany
    {
        return $this->hasMany(Playbox::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function partnershipReports(): HasMany
    {
        return $this->hasMany(PartnershipReport::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
