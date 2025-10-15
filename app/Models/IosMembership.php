<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IosMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'transaction_id',
        'original_transaction_id',
        'environment',
        'purchase_date',
        'expires_date',
        'is_trial_period',
        'is_in_intro_offer_period',
        'subscription_group_identifier',
        'auto_renew_status',
        'auto_renew_product_id',
        'receipt_data',
        'raw_response',
        'status',
    ];
}
