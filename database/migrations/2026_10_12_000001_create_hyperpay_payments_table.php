<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hyperpay_payments', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 24)->unique();
            $table->string('session_token', 64)->nullable()->index();
            $table->string('config_hash', 64)->nullable()->index();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->string('billing_period', 20)->nullable();
            $table->string('coupon_code', 50)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('SAR');
            $table->string('merchant_transaction_id', 64)->unique();
            $table->string('checkout_id', 64)->nullable()->index();
            $table->string('integrity', 191)->nullable();
            $table->string('payment_id', 64)->nullable()->index();
            $table->string('brand', 32)->nullable();
            $table->string('result_code', 64)->nullable();
            $table->text('result_description')->nullable();
            $table->string('status', 24)->default('pending')->index();
            $table->string('source', 24)->default('registration')->index();
            $table->string('resource_path', 255)->nullable();
            $table->json('billing')->nullable();
            $table->json('plan_config')->nullable();
            $table->json('quote')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hyperpay_payments');
    }
};
