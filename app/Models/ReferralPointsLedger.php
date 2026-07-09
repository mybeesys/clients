<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralPointsLedger extends Model
{
    protected $table = 'referral_points_ledger';

    protected $fillable = [
        'referral_code_id',
        'referral_conversion_id',
        'points',
        'reason',
    ];

    public function referralCode(): BelongsTo
    {
        return $this->belongsTo(ReferralCode::class);
    }

    public function conversion(): BelongsTo
    {
        return $this->belongsTo(ReferralConversion::class, 'referral_conversion_id');
    }
}
