<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $fillable = ['source', 'reference_id', 'category_id', 'amount', 'description', 'date'];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(FinanceCategory::class, 'category_id');
    }
}
