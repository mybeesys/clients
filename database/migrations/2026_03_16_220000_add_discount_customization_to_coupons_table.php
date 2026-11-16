<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('apply_mode', 20)->default('full')->after('value');
            $table->unsignedTinyInteger('apply_months')->nullable()->after('apply_mode');
            $table->decimal('max_discount', 10, 2)->nullable()->after('apply_months');
            $table->string('applicable_period', 10)->nullable()->after('max_discount');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['apply_mode', 'apply_months', 'max_discount', 'applicable_period']);
        });
    }
};
