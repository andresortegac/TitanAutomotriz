<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBarcode extends Model
{
    public const TYPE_CODE128 = 'CODE128';
    public const TYPE_EAN13 = 'EAN13';
    public const TYPE_EAN8 = 'EAN8';
    public const TYPE_UPCA = 'UPCA';

    protected $fillable = [
        'product_id',
        'code',
        'type',
        'source',
        'is_primary',
        'active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
