<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalPayment extends Model
{
    protected $fillable = ['rental_id', 'amount', 'payment_method', 'payment_date'];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }
}
