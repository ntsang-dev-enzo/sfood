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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->text('description_en')->nullable()->after('description');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->text('short_description_en')->nullable()->after('short_description');
            $table->longText('description_en')->nullable()->after('description');
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->longText('description_en')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'description_en']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'short_description_en', 'description_en']);
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'description_en']);
        });
    }
};
