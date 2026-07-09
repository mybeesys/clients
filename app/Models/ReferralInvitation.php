<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralInvitation extends Model
{
    protected $fillable = [
        'referral_code_id',
        'channel',
        'recipient_emails',
        'sender_device_hash',
    ];

    protected function casts(): array
    {
        return [
            'recipient_emails' => 'array',
        ];
    }

    public function referralCode(): BelongsTo
    {
        return $this->belongsTo(ReferralCode::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(ReferralVisit::class);
    }
}
