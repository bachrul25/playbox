<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalSession extends Model
{
    protected $fillable = ['rental_id', 'rental_unit_id', 'start_time', 'end_time', 'additional_minutes', 'status'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    public function unit()
    {
        return $this->belongsTo(RentalUnit::class, 'rental_unit_id');
    }
}
