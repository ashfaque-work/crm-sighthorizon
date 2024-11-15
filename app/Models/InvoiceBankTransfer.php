<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceBankTransfer extends Model
{
    protected $fillable = [
        'id',
        'invoice_id',
        'order_id',
        'amount',
        'status',
        'receipt',
        'date',
        'created_by',
    ];

    // public function payment()
    // {
    //     return $this->hasOne('App\Models\Payment', 'id', 'payment_id');
    // }

    // public function invoice()
    // {
    //     return $this->hasOne('App\Models\Invoice', 'id', 'invoice_id');
    // }
}
