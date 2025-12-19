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
        Schema::create('question_set_commands', function (Blueprint $table) {
            $table->id();
            $table->string('command')->unique(); // Ví dụ: /start, /survey, /feedback
            $table->foreignId('question_set_id')->constrained()->onDelete('cascade');
            $table->text('response_message')->nullable(); // Tin nhắn trả về khi gõ command
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('command');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_set_commands');
    }
};
