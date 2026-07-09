<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralCode extends Model
{
    protected $fillable = [
        'code',
        'tenant_id',
        'employee_id',
        'employee_name',
        'employee_email',
        'sender_device_hash',
        'custom_promotional_text_ar',
        'custom_promotional_text_en',
        'is_active',
        'total_points',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ReferralInvitation::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(ReferralVisit::class);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(ReferralConversion::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(ReferralPointsLedger::class);
    }

    public function inviteUrl(): string
    {
        return url('/invite/'.$this->code);
    }
}
