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
        Schema::table('features', function (Blueprint $table) {
            $table->string('unique_name')->after('id')->unique();
            $table->string('name_ar')->nullable()->after('name');
            $table->string('description_ar')->nullable()->after('description');
            $table->boolean('countable')->after('id')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('features', function (Blueprint $table) {
            $table->dropColumn('unique_name');
            $table->dropColumn('name_ar');
            $table->dropColumn('description_ar');
            $table->dropColumn('countable');
        });
    }
};
