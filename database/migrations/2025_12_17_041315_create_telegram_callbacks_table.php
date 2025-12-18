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
        Schema::create('telegram_callbacks', function (Blueprint $table) {
            $table->id();
            $table->string('callback_id')->unique();
            $table->string('callback_data');
            $table->string('telegram_user_id');
            $table->string('telegram_username')->nullable();
            $table->string('telegram_first_name')->nullable();
            $table->string('telegram_last_name')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->bigInteger('message_id')->nullable();
            $table->text('message_text')->nullable();
            $table->bigInteger('chat_id')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->index('callback_data');
            $table->index('telegram_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_callbacks');
    }
};
