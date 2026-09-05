<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name', 'document', 'phone', 'email', 'address',
        'identification_document_code', 'dv', 'legal_organization_code',
        'trade_name', 'tribute_code', 'responsibilities', 'country_code',
        'municipality_code',
    ];

    protected $casts = [
        'responsibilities' => 'array',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function creditPayments()
    {
        return $this->hasMany(CreditPayment::class);
    }
}
