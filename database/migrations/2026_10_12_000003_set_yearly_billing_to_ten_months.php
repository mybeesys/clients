<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('entitlement_settings')) {
            return;
        }

        DB::table('entitlement_settings')->updateOrInsert(
            ['key' => 'yearly_months_charged'],
            ['value' => '10', 'updated_at' => now(), 'created_at' => now()]
        );

        Cache::forget('entitlement_catalog_settings');
        Cache::forget('entitlement_catalog_products');
    }

    public function down(): void
    {
        if (! Schema::hasTable('entitlement_settings')) {
            return;
        }

        DB::table('entitlement_settings')
            ->where('key', 'yearly_months_charged')
            ->update(['value' => '12', 'updated_at' => now()]);

        Cache::forget('entitlement_catalog_settings');
    }
};
