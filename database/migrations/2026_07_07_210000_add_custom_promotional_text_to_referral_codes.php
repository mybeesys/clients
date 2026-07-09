<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referral_codes', function (Blueprint $table) {
            $table->text('custom_promotional_text_ar')->nullable()->after('sender_device_hash');
            $table->text('custom_promotional_text_en')->nullable()->after('custom_promotional_text_ar');
        });
    }

    public function down(): void
    {
        Schema::table('referral_codes', function (Blueprint $table) {
            $table->dropColumn(['custom_promotional_text_ar', 'custom_promotional_text_en']);
        });
    }
};
