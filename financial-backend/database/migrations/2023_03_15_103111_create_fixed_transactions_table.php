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
        Schema::create('fixed_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->date('start_date');
            $table->unsignedDecimal('amount');
            $table->enum('schedule', ['weekly', 'monthly','yearly']);
            $table->date('next_payment_date')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->unsignedBigInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('fixed_keys');
            $table->unsignedBigInteger('fixed_key_id');
            $table->foreign('fixed_key_id')->references('id')->on('fixed_key');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_transactions');
    }
};