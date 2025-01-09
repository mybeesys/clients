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
            $table->string('name_ar')->nullable()->after('name');
            $table->string('description_ar')->nullable()->after('description');
            $table->integer('discount')->after('periodicity_type')->nullable();
            $table->string('discount_period_type')->after('discount')->nullable();
            $table->decimal('price_after_discount', 10, 2)->after('discount_period_type')->nullable();

            // $table->json('specifications')->after('active')->nullable();
            // $table->dropColumn('periodicity');
            // $table->dropColumn('periodicity_type');
            // $table->dropColumn('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('name_ar');
            $table->dropColumn('description_ar');
            $table->dropColumn('discount');
            $table->dropColumn('discount_period_type');
            $table->dropColumn('price_after_discount');
            // $table->dropColumn('specifications');
            // $table->decimal('price', 10, 2)->after('active')->nullable();
            // $table->integer('periodicity')->after('price')->unsigned()->nullable();
            // $table->string('periodicity_type')->after('periodicity')->nullable();
        });
    }
};
