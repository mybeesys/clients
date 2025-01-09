<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('feature_plan', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
        Schema::table('features', function (Blueprint $table) {
            $table->dropColumn('countable');
            $table->renameColumn('name', 'name_en');
            $table->renameColumn('unique_name', 'name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feature_plan', function (Blueprint $table) {
            $table->integer('amount')->after('charges')->default(1);
        });
        Schema::table('features', function (Blueprint $table) {
            $table->boolean('countable')->after('id')->default(false);
            $table->renameColumn('name', 'unique_name');
            $table->renameColumn('name_en', 'name');
        });
    }
};
