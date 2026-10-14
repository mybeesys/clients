<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_entitlements', function (Blueprint $table) {
            if (! Schema::hasColumn('company_entitlements', 'pos_devices_quota')) {
                $table->unsignedInteger('pos_devices_quota')->default(0)->after('screen_devices_quota');
            }
            if (! Schema::hasColumn('company_entitlements', 'waiter_seats_quota')) {
                $table->unsignedInteger('waiter_seats_quota')->default(0)->after('pos_devices_quota');
            }
        });
    }

    public function down(): void
    {
        Schema::table('company_entitlements', function (Blueprint $table) {
            if (Schema::hasColumn('company_entitlements', 'waiter_seats_quota')) {
                $table->dropColumn('waiter_seats_quota');
            }
            if (Schema::hasColumn('company_entitlements', 'pos_devices_quota')) {
                $table->dropColumn('pos_devices_quota');
            }
        });
    }
};
