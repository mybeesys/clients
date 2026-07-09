<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReferralVisit extends Model
{
    protected $fillable = [
        'referral_code_id',
        'referral_invitation_id',
        'visitor_device_hash',
        'ip_address',
        'user_agent',
        'is_distinct_device',
        'session_id',
    ];

    protected function casts(): array
    {
        return [
            'is_distinct_device' => 'boolean',
        ];
    }

    public function referralCode(): BelongsTo
    {
        return $this->belongsTo(ReferralCode::class);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(ReferralInvitation::class, 'referral_invitation_id');
    }

    public function conversion(): HasOne
    {
        return $this->hasOne(ReferralConversion::class);
    }
}
