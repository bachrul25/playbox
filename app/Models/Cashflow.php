<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cashflow extends Model
{
    protected $fillable = ['type', 'source', 'reference_id', 'amount', 'description', 'date'];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];
}
