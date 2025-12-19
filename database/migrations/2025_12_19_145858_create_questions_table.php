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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_set_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0); // Thứ tự câu hỏi
            $table->text('question_text'); // Nội dung câu hỏi
            $table->string('field_name'); // Tên field để lưu trữ (ví dụ: name, phone, issue)
            $table->string('validation_rule')->nullable(); // Rule validation (ví dụ: phone, email, required)
            $table->text('error_message')->nullable(); // Thông báo lỗi khi validation fail
            $table->boolean('is_required')->default(true); // Câu hỏi bắt buộc hay không
            $table->json('options')->nullable(); // Các tùy chọn (cho câu hỏi multiple choice)
            $table->timestamps();

            $table->index(['question_set_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
