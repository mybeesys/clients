<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('saved_payment_cards')) {
            return;
        }

        Schema::create('saved_payment_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('hyperpay_payment_id')->nullable()->constrained('hyperpay_payments')->nullOnDelete();
            $table->string('registration_id', 64);
            $table->string('payment_id', 64)->nullable();
            $table->string('brand', 32)->nullable();
            $table->string('card_bin', 16)->nullable();
            $table->string('last_four', 4)->nullable();
            $table->string('holder', 120)->nullable();
            $table->string('expiry_month', 2)->nullable();
            $table->string('expiry_year', 4)->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('status', 20)->default('active');
            $table->json('gateway_payload')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'registration_id']);
            $table->index(['company_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_payment_cards');
    }
};
