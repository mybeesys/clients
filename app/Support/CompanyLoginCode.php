<?php

namespace App\Support;

use App\Models\Company;
use Illuminate\Support\Facades\DB;

class CompanyLoginCode
{
    public static function generateUnique(?int $ignoreCompanyId = null): string
    {
        do {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $exists = Company::query()
                ->when($ignoreCompanyId, fn ($query) => $query->where('id', '!=', $ignoreCompanyId))
                ->where('login_code', $code)
                ->exists();
        } while ($exists);

        return $code;
    }

    public static function ensure(Company $company): string
    {
        if (filled($company->login_code) && preg_match('/^\d{6}$/', (string) $company->login_code)) {
            return (string) $company->login_code;
        }

        $code = static::generateUnique($company->id);

        DB::table('companies')
            ->where('id', $company->id)
            ->update(['login_code' => $code]);

        $company->login_code = $code;

        return $code;
    }
}
