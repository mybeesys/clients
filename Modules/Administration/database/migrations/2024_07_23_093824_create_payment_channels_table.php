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
        Schema::create('payment_channels', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('status', ['active', 'inactive']);
            $table->string('class_name');
            $table->text('currencies')->nullable();
            $table->string('image')->nullable();
            $table->text('settings')->nullable();
            $table->integer('created_at')->unsigned();
            $table->string('disabled_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_channels');
    }
};
