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
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');

        Schema::disableForeignKeyConstraints();
        DB::table('countries')->truncate();
        Schema::enableForeignKeyConstraints();

        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['status', 'deleted_at', 'name', 'iso2', 'iso3', 'numcode', 'phonecode']);
            $table->string('iso_code')->after('id');
            $table->string('name_en')->after('iso_code');
            $table->string('name_ar')->after('name_en');
            $table->string('dial_code')->after('name_ar');
            $table->string('currency_name_en')->nullable()->after('dial_code');
            $table->string('currency_symbol_en')->nullable()->after('currency_name_en');
            $table->string('currency_name_ar')->nullable()->after('currency_symbol_en');
            $table->string('currency_symbol_ar')->nullable()->after('currency_name_ar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn([
                'iso_code',
                'name_en',
                'name_ar',
                'dial_code',
                'currency_name_en',
                'currency_symbol_en',
                'currency_name_ar',
                'currency_symbol_ar',
            ]);
            $table->string('name', 255)->after('id');
            $table->string('iso2')->nullable();
            $table->string('iso3')->nullable();
            $table->string('numcode')->nullable();
            $table->string('phonecode')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->softDeletes();
        });
    }
};
