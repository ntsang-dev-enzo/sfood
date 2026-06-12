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
        Schema::table('wheel_prizes', function (Blueprint $table) {
            $table->json('applicable_category_ids')->nullable();
            $table->json('applicable_product_ids')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wheel_prizes', function (Blueprint $table) {
            $table->dropColumn(['applicable_category_ids', 'applicable_product_ids']);
        });
    }
};
