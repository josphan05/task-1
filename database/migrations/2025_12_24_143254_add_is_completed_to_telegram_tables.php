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
        Schema::table('telegram_callbacks', function (Blueprint $table) {
            $table->boolean('is_completed')->default(false)->after('raw_data');
            $table->foreignId('telegram_conversation_id')->nullable()->after('is_completed')->constrained('telegram_conversations')->nullOnDelete();
            $table->index('is_completed');
            $table->index('telegram_conversation_id');
        });

        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->boolean('is_completed')->default(false)->after('raw_data');
            $table->foreignId('telegram_conversation_id')->nullable()->after('is_completed')->constrained('telegram_conversations')->nullOnDelete();
            $table->index('is_completed');
            $table->index('telegram_conversation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_callbacks', function (Blueprint $table) {
            $table->dropForeign(['telegram_conversation_id']);
            $table->dropIndex(['is_completed']);
            $table->dropIndex(['telegram_conversation_id']);
            $table->dropColumn(['is_completed', 'telegram_conversation_id']);
        });

        Schema::table('telegram_messages', function (Blueprint $table) {
            $table->dropForeign(['telegram_conversation_id']);
            $table->dropIndex(['is_completed']);
            $table->dropIndex(['telegram_conversation_id']);
            $table->dropColumn(['is_completed', 'telegram_conversation_id']);
        });
    }
};
