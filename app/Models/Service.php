<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'sale_price',
        'tax_rate',
        'active',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
}
