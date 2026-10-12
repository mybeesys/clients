<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class HyperPayPayment extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_CHECKOUT = 'checkout';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    public const STATUS_PENDING_GATEWAY = 'pending_gateway';

    public const SOURCE_REGISTRATION = 'registration';

    public const SOURCE_SUBSCRIPTION = 'subscription';

    protected $table = 'hyperpay_payments';

    protected $guarded = [];

    protected $casts = [
        'amount' => 'float',
        'billing' => 'array',
        'plan_config' => 'array',
        'quote' => 'array',
        'request_payload' => 'array',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $payment): void {
            if (blank($payment->uid)) {
                $payment->uid = strtoupper(Str::random(16));
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CHECKOUT, self::STATUS_PENDING_GATEWAY], true);
    }

    public function matchesPlanConfig(array $planConfig): bool
    {
        return hash_equals((string) $this->config_hash, self::hashPlanConfig($planConfig));
    }

    public static function hashPlanConfig(array $planConfig): string
    {
        $normalized = [
            'period' => ($planConfig['period'] ?? 'Month') === 'Year' ? 'Year' : 'Month',
            'modules' => array_values(array_unique(array_filter($planConfig['modules'] ?? []))),
            'employees' => (int) ($planConfig['employees'] ?? 0),
            'establishments' => (int) ($planConfig['establishments'] ?? 0),
            'screen_devices' => (int) ($planConfig['screen_devices'] ?? 0),
            'coupon_code' => isset($planConfig['coupon_code']) ? strtoupper(trim((string) $planConfig['coupon_code'])) : '',
        ];

        sort($normalized['modules']);

        return hash('sha256', json_encode($normalized));
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => __('fields.hyperpay_status_paid'),
            self::STATUS_FAILED => __('fields.hyperpay_status_failed'),
            self::STATUS_CHECKOUT => __('fields.hyperpay_status_checkout'),
            self::STATUS_PENDING_GATEWAY => __('fields.hyperpay_status_pending_gateway'),
            default => __('fields.hyperpay_status_pending'),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_PENDING_GATEWAY => 'warning',
            self::STATUS_CHECKOUT => 'info',
            default => 'gray',
        };
    }
}
