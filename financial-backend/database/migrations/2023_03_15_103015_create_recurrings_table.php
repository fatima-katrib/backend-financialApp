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
        Schema::create('recurrings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description');
            $table->decimal('amount')->unsigned()->default(0);
            $table->unsignedBigInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->date('end_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->date('start_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurrings');
    }
};