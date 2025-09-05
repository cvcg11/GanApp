<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'stock'
    ];
    
    protected $casts = [
        'stock' => 'integer',
    ];

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'invoice_product')
                    ->withPivot(['quantity','unit_price','subtotal'])
                    ->withTimestamps();
    }

}
