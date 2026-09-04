<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'document', 'phone', 'email', 'address'];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function creditPayments()
    {
        return $this->hasMany(CreditPayment::class);
    }
}
