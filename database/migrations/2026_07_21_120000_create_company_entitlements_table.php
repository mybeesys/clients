<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->json('modules');
            $table->unsignedInteger('employees_quota')->default(5);
            $table->unsignedInteger('establishments_quota')->default(1);
            $table->string('period', 16)->default('Month');
            $table->string('currency', 8)->default('SAR');
            $table->decimal('monthly_subtotal', 12, 2)->default(0);
            $table->decimal('period_total', 12, 2)->default(0);
            $table->json('line_items')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_entitlements');
    }
};
