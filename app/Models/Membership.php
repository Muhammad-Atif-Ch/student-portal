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
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'status' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get active memberships.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope to get expired memberships.
     */
    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now())
            ->where('status', true);
    }

    /**
     * Scope to get valid memberships (active and not expired).
     */
    public function scopeValid($query)
    {
        return $query->where('status', true)
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>', now());
            });
    }

    /**
     * Check if membership is expired.
     */
    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date < now();
    }

    /**
     * Check if membership is valid.
     */
    public function isValid(): bool
    {
        return $this->status && !$this->isExpired();
    }

    /**
     * Get days remaining in membership.
     */
    public function getDaysRemaining(): int
    {
        if (!$this->end_date || $this->isExpired()) {
            return 0;
        }

        return now()->diffInDays($this->end_date);
    }

    /**
     * Deactivate membership.
     */
    public function deactivate(): bool
    {
        return $this->update(['status' => false]);
    }

    /**
     * Extend membership.
     */
    public function extend(string $duration = '1 month'): bool
    {
        $newEndDate = $this->end_date ?
            $this->end_date->modify('+' . $duration) :
            now()->modify('+' . $duration);

        return $this->update(['end_date' => $newEndDate]);
    }


}
