<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('login_code', 6)->nullable()->unique()->after('id');
        });

        $used = [];

        DB::table('companies')
            ->orderBy('id')
            ->select(['id', 'login_code'])
            ->chunkById(100, function ($companies) use (&$used): void {
                foreach ($companies as $company) {
                    if (filled($company->login_code)) {
                        $used[$company->login_code] = true;

                        continue;
                    }

                    $code = $this->uniqueCode($used);
                    $used[$code] = true;

                    DB::table('companies')
                        ->where('id', $company->id)
                        ->update(['login_code' => $code]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropUnique(['login_code']);
            $table->dropColumn('login_code');
        });
    }

    /**
     * @param  array<string, bool>  $used
     */
    private function uniqueCode(array &$used): string
    {
        do {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (isset($used[$code]));

        return $code;
    }
};
