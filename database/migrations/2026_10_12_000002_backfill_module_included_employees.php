<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('entitlement_products')) {
            return;
        }

        DB::table('entitlement_products')
            ->where('type', 'module')
            ->whereNull('included')
            ->update(['included' => 1]);
    }

    public function down(): void
    {
        // Keep admin-configured seat counts.
    }
};
