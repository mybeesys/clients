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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->integer('user_id');
            $table->string('ceo_name')->nullable();
            $table->integer('phone')->nullable();
            $table->string('zipcode')->nullable();
            $table->string('national_address')->nullable();
            $table->string('website')->nullable();
            $table->integer('country_id');
            $table->string('tax_name')->nullable();
            $table->string('logo')->nullable();
            $table->tinyInteger('subscribed')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
