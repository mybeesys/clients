<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entitlement_products', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('type', 32); // platform | module | quota
            $table->string('group', 64)->nullable();
            $table->string('name_en');
            $table->string('name_ar');
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->decimal('price_month', 12, 2)->default(0);
            $table->decimal('price_per_extra_month', 12, 2)->nullable();
            $table->unsignedInteger('included')->nullable();
            $table->unsignedInteger('min')->nullable();
            $table->unsignedInteger('max')->nullable();
            $table->json('requires')->nullable();
            /** Quota only shown when this module is selected (e.g. screen_devices → digital_screens). */
            $table->string('linked_module')->nullable()->index();
            $table->string('icon', 64)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('entitlement_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::table('company_entitlements', function (Blueprint $table) {
            $table->unsignedInteger('screen_devices_quota')->default(0)->after('establishments_quota');
        });
    }

    public function down(): void
    {
        Schema::table('company_entitlements', function (Blueprint $table) {
            $table->dropColumn('screen_devices_quota');
        });

        Schema::dropIfExists('entitlement_settings');
        Schema::dropIfExists('entitlement_products');
    }
};
