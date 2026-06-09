<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('flash_sale_price', 12, 2)->nullable()->after('sale_price');
            $table->dateTime('flash_sale_starts_at')->nullable()->after('flash_sale_price');
            $table->dateTime('flash_sale_ends_at')->nullable()->after('flash_sale_starts_at');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['flash_sale_price', 'flash_sale_starts_at', 'flash_sale_ends_at']);
        });
    }
};
