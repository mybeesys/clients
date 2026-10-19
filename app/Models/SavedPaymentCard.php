<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavedPaymentCard extends Model
{
    use SoftDeletes;

    protected $table = 'saved_payment_cards';

    protected $guarded = [];

    protected $casts = [
        'is_default' => 'boolean',
        'gateway_payload' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hyperpayPayment(): BelongsTo
    {
        return $this->belongsTo(HyperPayPayment::class, 'hyperpay_payment_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function label(): string
    {
        if (filled($this->nickname)) {
            return (string) $this->nickname;
        }

        $brand = strtoupper((string) ($this->brand ?: 'CARD'));
        $last = $this->last_four ? '•••• '.$this->last_four : '••••';

        return trim($brand.' '.$last);
    }

    public function expiryLabel(): string
    {
        if (! $this->expiry_month || ! $this->expiry_year) {
            return '—';
        }

        $year = strlen((string) $this->expiry_year) === 4
            ? substr((string) $this->expiry_year, -2)
            : (string) $this->expiry_year;

        return str_pad((string) $this->expiry_month, 2, '0', STR_PAD_LEFT).'/'.$year;
    }
}
