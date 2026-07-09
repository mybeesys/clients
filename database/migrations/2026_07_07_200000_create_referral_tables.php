<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_program_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('default_points_per_conversion')->default(10);
            $table->json('points_by_plan')->nullable();
            $table->text('promotional_template_ar')->nullable();
            $table->text('promotional_template_en')->nullable();
            $table->unsignedInteger('monthly_points_cap')->nullable();
            $table->timestamps();
        });

        Schema::create('referral_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('employee_name')->nullable();
            $table->string('employee_email')->nullable()->index();
            $table->string('sender_device_hash', 64)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('total_points')->default(0);
            $table->timestamps();
        });

        Schema::create('referral_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_code_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 20);
            $table->json('recipient_emails')->nullable();
            $table->string('sender_device_hash', 64)->nullable();
            $table->timestamps();
        });

        Schema::create('referral_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referral_invitation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('visitor_device_hash', 64)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_distinct_device')->default(false);
            $table->string('session_id', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('referral_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referral_visit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subscriber_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('points_awarded')->default(0);
            $table->boolean('is_distinct_device')->default(false);
            $table->string('status', 20)->default('confirmed');
            $table->timestamps();
        });

        Schema::create('referral_points_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referral_conversion_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('points');
            $table->string('reason');
            $table->timestamps();
        });

        DB::table('referral_program_settings')->insert([
            'is_enabled' => true,
            'default_points_per_conversion' => 10,
            'promotional_template_ar' => "جرّب نظام My Bee لإدارة أعمالك باحترافية!\nسجّل عبر رابطي واحصل على أفضل تجربة:\n{link}",
            'promotional_template_en' => "Try My Bee to run your business professionally!\nRegister using my link:\n{link}",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_points_ledger');
        Schema::dropIfExists('referral_conversions');
        Schema::dropIfExists('referral_visits');
        Schema::dropIfExists('referral_invitations');
        Schema::dropIfExists('referral_codes');
        Schema::dropIfExists('referral_program_settings');
    }
};
