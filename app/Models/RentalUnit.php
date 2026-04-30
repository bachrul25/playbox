<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalUnit extends Model
{
    protected $fillable = ['code', 'name', 'type', 'hourly_price', 'status', 'location', 'description'];

    protected $casts = [
        'hourly_price' => 'decimal:2',
    ];

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function activeRental()
    {
        return $this->hasOne(Rental::class)->where('status', 'active');
    }
}
