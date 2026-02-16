<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    protected $fillable = [
        'currency',
        'rate_to_usd',
        'usd_to_rate',
        'last_updated_at',
    ];
}
