<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'city',
        'address',
        'mobile',
        'image',
        'device_id',
        'language_id',
        'fcm_token'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all memberships for the user.
     */
    public function membership(): HasOne
    {
        return $this->hasOne(Membership::class);
    }

    /**
     * Get the active and valid membership for the user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function activeMembership(): HasOne
    {
        return $this->hasOne(Membership::class)
            ->ofMany(['start_date' => 'max'], function ($query) {
                $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                            ->orWhere('end_date', '>', now());
                    });
            });
    }

    /**
     * Get the current valid membership.
     */
    public function currentMembership(): ?Membership
    {
        return $this->memberships()
            ->valid()
            ->latest()
            ->first();
    }

    /**
     * Check if user has active membership.
     */
    public function hasActiveMembership(): bool
    {
        return $this->currentMembership() !== null;
    }

    /**
     * Check if user can access the app.
     */
    public function canAccessApp(): bool
    {
        return $this->hasActiveMembership();
    }

    /**
     * Get membership status with details.
     */
    public function getMembershipStatus(): array
    {
        $membership = $this->currentMembership();

        if (!$membership) {
            return [
                'can_access' => false,
                'message' => 'No active membership found',
                'membership_type' => null,
                'end_date' => null,
                'days_remaining' => 0
            ];
        }

        if ($membership->isExpired()) {
            // Deactivate expired membership
            $membership->deactivate();

            return [
                'can_access' => false,
                'message' => 'Membership has expired',
                'membership_type' => $membership->getAttribute('membership_type'),
                'end_date' => $membership->end_date,
                'days_remaining' => 0
            ];
        }

        return [
            'can_access' => true,
            'message' => 'Access granted',
            'membership_type' => $membership->getAttribute('membership_type'),
            'end_date' => $membership->end_date,
            'days_remaining' => $membership->getDaysRemaining()
        ];
    }

    /**
     * Create free membership for user.
     */
    public function createFreeMembership(): Membership
    {
        // Deactivate existing active memberships
        $this->memberships()->active()->update(['is_active' => false]);

        return $this->memberships()->create([
            'membership_type' => 'free',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'is_active' => true
        ]);
    }
}
