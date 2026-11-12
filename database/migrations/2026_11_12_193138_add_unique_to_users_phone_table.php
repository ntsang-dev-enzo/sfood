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
        // Resolve any existing duplicate non-null phone numbers before applying unique index
        $duplicates = \Illuminate\Support\Facades\DB::table('users')
            ->select('phone')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->groupBy('phone')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('phone');

        foreach ($duplicates as $phone) {
            $userIds = \Illuminate\Support\Facades\DB::table('users')
                ->where('phone', $phone)
                ->orderBy('id')
                ->pluck('id');
            // Keep the first user, set duplicates to null
            $userIds->shift();
            \Illuminate\Support\Facades\DB::table('users')
                ->whereIn('id', $userIds)
                ->update(['phone' => null]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('phone', 'users_phone_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_phone_unique');
        });
    }
};
