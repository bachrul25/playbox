<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    protected $fillable = [
        'user_id', 'rental_unit_id', 'invoice_number', 'customer_name',
        'start_time', 'end_time', 'duration_minutes', 'hourly_price',
        'total_price', 'payment_method', 'status', 'mode', 'planned_minutes', 'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'hourly_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'planned_minutes' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unit()
    {
        return $this->belongsTo(RentalUnit::class, 'rental_unit_id');
    }

    public function sessions()
    {
        return $this->hasMany(RentalSession::class);
    }

    public function payment()
    {
        return $this->hasOne(RentalPayment::class);
    }
}
