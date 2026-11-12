<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_auto_replies', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('group_name')->index();
            $table->string('question');
            $table->text('answer');
            $table->json('keywords');
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('hit_count')->default(0);
            $table->timestamps();
        });

        Schema::create('chat_auto_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('message');
            $table->foreignId('matched_reply_id')->nullable()
                ->constrained('chat_auto_replies')->nullOnDelete();
            $table->decimal('score', 5, 4)->default(0);
            $table->boolean('is_fallback')->default(false);
            $table->timestamps();
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->boolean('is_auto')->default(false)->after('is_read');
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn('is_auto');
        });

        Schema::dropIfExists('chat_auto_logs');
        Schema::dropIfExists('chat_auto_replies');
    }
};
