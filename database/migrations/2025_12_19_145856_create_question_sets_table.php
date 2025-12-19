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
        Schema::create('question_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên bộ câu hỏi
            $table->text('description')->nullable(); // Mô tả
            $table->text('start_message')->nullable(); // Tin nhắn bắt đầu (mặc định: "Xin chào! Tôi là bot hỗ trợ.")
            $table->text('completion_message')->nullable(); // Tin nhắn khi hoàn thành (mặc định: "Cảm ơn, thông tin đã được ghi nhận.")
            $table->json('completion_buttons')->nullable(); // Các nút inline keyboard khi hoàn thành
            $table->boolean('is_active')->default(true); // Bộ câu hỏi có đang hoạt động không
            $table->boolean('is_default')->default(false); // Bộ câu hỏi mặc định
            $table->timestamps();

            $table->index('is_active');
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_sets');
    }
};
