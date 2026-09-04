<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name', 'nit', 'phone', 'email', 'address', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
