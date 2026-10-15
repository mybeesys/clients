<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_entitlements', function (Blueprint $table) {
            if (! Schema::hasColumn('company_entitlements', 'kitchen_devices_quota')) {
                $table->unsignedInteger('kitchen_devices_quota')->default(0)->after('waiter_seats_quota');
            }
        });
    }

    public function down(): void
    {
        Schema::table('company_entitlements', function (Blueprint $table) {
            if (Schema::hasColumn('company_entitlements', 'kitchen_devices_quota')) {
                $table->dropColumn('kitchen_devices_quota');
            }
        });
    }
};
