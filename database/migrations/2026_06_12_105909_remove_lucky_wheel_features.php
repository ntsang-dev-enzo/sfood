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
        Schema::dropIfExists('wheel_prizes');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'lucky_spins')) {
                $table->dropColumn('lucky_spins');
            }
            if (Schema::hasColumn('users', 'last_free_spin_at')) {
                $table->dropColumn('last_free_spin_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse logic needed, data is lost anyway
    }
};
