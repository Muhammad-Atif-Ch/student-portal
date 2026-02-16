<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IosMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'membership_type',
        'product_id',
        'transaction_id',
        'original_transaction_id',
        'environment',
        'purchase_date',
        'expires_date',
        'price',
        'currency',
        'subscription_group_identifier',
        'auto_renew_status',
        'auto_renew_product_id',
        'raw_response',
        'status',
    ];

    public function currencyRate()
    {
        return $this->belongsTo(CurrencyRate::class, 'currency', 'currency');
    }
}
