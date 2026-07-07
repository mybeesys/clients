<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('role', 32)->default('owner');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'company_id']);
        });

        if (Schema::hasTable('companies') && Schema::hasColumn('companies', 'user_id')) {
            $rows = DB::table('companies')
                ->whereNotNull('user_id')
                ->whereNull('deleted_at')
                ->get(['id', 'user_id']);

            foreach ($rows as $row) {
                DB::table('company_user')->insertOrIgnore([
                    'user_id' => $row->user_id,
                    'company_id' => $row->id,
                    'role' => 'owner',
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_user');
    }
};
