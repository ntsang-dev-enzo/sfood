<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Một số bản sao cơ sở dữ liệu đã có sẵn cột này nhưng chưa ghi vào bảng migrations.
        if (Schema::hasColumn('categories', 'icon')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->string('icon')->nullable()->after('thumbnail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('categories', 'icon')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
