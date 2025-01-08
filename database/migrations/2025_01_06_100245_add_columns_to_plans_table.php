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
            $table->json('specifications')->after('active')->nullable();
            $table->dropColumn('periodicity');
            $table->dropColumn('periodicity_type');
            $table->dropColumn('price');
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
            $table->dropColumn('specifications');
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('periodicity')->unsigned()->nullable();
            $table->string('periodicity_type')->nullable();
        });
    }
};
