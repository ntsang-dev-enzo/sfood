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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('reward_points')->default(0)->after('role');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('points_used')->default(0)->after('coupon_id');
            $table->decimal('points_discount', 12, 2)->default(0)->after('points_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('reward_points');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['points_used', 'points_discount']);
        });
    }
};
