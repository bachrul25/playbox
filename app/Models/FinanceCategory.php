<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceCategory extends Model
{
    protected $fillable = ['name', 'type', 'description', 'status'];

    public function incomes()
    {
        return $this->hasMany(Income::class, 'category_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'category_id');
    }
}
