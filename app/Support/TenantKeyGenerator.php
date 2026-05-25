<?php

namespace App\Support;

use Illuminate\Support\Str;

class TenantKeyGenerator
{
    public static function fromEnglishName(?string $englishName): string
    {
        $ascii = Str::lower(Str::ascii(trim($englishName ?? '')));
        $key = preg_replace('/[^a-z0-9]/', '', $ascii);

        return filled($key) ? $key : 'tenant';
    }
}
