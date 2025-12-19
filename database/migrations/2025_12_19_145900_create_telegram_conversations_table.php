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
        Schema::create('telegram_conversations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_user_id')->index();
            $table->foreignId('question_set_id')->nullable()->constrained('question_sets')->nullOnDelete();
            $table->string('step')->nullable()->index(); // Lưu field_name của câu hỏi hiện tại
            $table->integer('current_question_order')->nullable(); // Thứ tự câu hỏi hiện tại
            $table->json('data')->nullable(); // Lưu câu trả lời
            $table->timestamps();

            $table->index(['telegram_user_id', 'step']);
            $table->index(['question_set_id', 'telegram_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_conversations');
    }
};
