<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'date',
        'total',
        'description',
    ];

    protected $casts = [
        'date'  => 'date',
        'total' => 'decimal:2',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'invoice_product')
            ->withPivot(['quantity','unit_price','subtotal'])
            ->withTimestamps();
    }
}   
