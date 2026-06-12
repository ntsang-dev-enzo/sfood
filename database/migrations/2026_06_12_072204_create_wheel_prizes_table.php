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
        Schema::create('wheel_prizes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('fixed'); // fixed, percent, free_shipping
            $table->decimal('value', 15, 2)->default(0);
            $table->decimal('min_order_amount', 15, 2)->default(0);
            $table->integer('weight')->default(10);
            $table->boolean('is_miss')->default(false); // Chúc bạn may mắn lần sau
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wheel_prizes');
    }
};
