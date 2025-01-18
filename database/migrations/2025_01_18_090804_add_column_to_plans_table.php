<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('discount_type')->after('periodicity_type')->nullable();
            $table->renameColumn('discount_period_type', 'discount_period_amount_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('discount_type');
            $table->renameColumn('discount_period_amount_type', 'discount_period_type');
        });
    }
};
