<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('saved_payment_cards')) {
            return;
        }

        Schema::table('saved_payment_cards', function (Blueprint $table) {
            if (! Schema::hasColumn('saved_payment_cards', 'nickname')) {
                $table->string('nickname', 80)->nullable()->after('holder');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('saved_payment_cards')) {
            return;
        }

        Schema::table('saved_payment_cards', function (Blueprint $table) {
            if (Schema::hasColumn('saved_payment_cards', 'nickname')) {
                $table->dropColumn('nickname');
            }
        });
    }
};
