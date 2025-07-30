<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'membership_type',
        'start_date',
        'end_date',
        'order_id',
        'auto_renewing',
        'price_currency_code',
        'price_amount_micros',
        'country_code',
        'cancel_reason',
        'purchase_type',
        'acknowledgement_state',
        'raw_response',
        'status',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'auto_renewing' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setRawResponseAttribute($value)
    {
        $this->attributes['raw_response'] = is_array($value) ? json_encode($value) : $value;
    }

    // Automatically convert JSON back to array when retrieving
    public function getRawResponseAttribute($value)
    {
        return json_decode($value, true);
    }


}
