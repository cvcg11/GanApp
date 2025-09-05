<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'name',
        'account_type',
        'current_balance',
        'target_amount',
        'due_date',
        'description'
    ];

     protected $casts = [
        'current_balance' => 'decimal:2',
        'target_amount'   => 'decimal:2',
        'due_date'        => 'date',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
