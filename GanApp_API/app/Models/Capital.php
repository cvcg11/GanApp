<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Capital extends Model
{
    public $timestamps = false;

    protected $table = 'capital';

    protected $fillable = 
    [
        'current_amount',
        'last_update'
    ];

    protected $casts = [
        'current_amount' => 'decimal:2',
        'last_update'    => 'datetime',
    ];
}
