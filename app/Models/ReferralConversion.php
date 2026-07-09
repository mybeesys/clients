<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralConversion extends Model
{
    protected $fillable = [
        'referral_code_id',
        'referral_visit_id',
        'company_id',
        'subscriber_user_id',
        'plan_id',
        'points_awarded',
        'is_distinct_device',
        'status',
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

    public function visit(): BelongsTo
    {
        return $this->belongsTo(ReferralVisit::class, 'referral_visit_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subscriber_user_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
