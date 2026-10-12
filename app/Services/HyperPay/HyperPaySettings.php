<?php

namespace App\Services\HyperPay;

use App\Support\AppSetting;

class HyperPaySettings
{
    /**
     * @return array<string, string>
     */
    public static function testDefaults(): array
    {
        $defaults = config('hyperpay.test_defaults', []);

        return is_array($defaults) ? $defaults : [];
    }

    public static function applyTestDefaults(): void
    {
        foreach (self::testDefaults() as $suffix => $value) {
            AppSetting::set(
                'hyperpay.'.$suffix,
                is_bool($value) ? ($value ? '1' : '0') : (string) $value
            );
        }
    }
}
