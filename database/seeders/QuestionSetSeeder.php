<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionSet;
use Illuminate\Database\Seeder;

class QuestionSetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo bộ câu hỏi mặc định
        $questionSet = QuestionSet::create([
            'name' => 'Form Phản Ánh',
            'description' => 'Bộ câu hỏi thu thập thông tin phản ánh từ người dùng',
            'start_message' => 'Xin chào! Tôi là bot hỗ trợ. nHãy trả lời các câu hỏi sau:',
            'completion_message' => 'Cảm ơn, thông tin đã được ghi nhận.',
            'completion_buttons' => [
                [
                    [
                        'text' => '✅ Xác nhận và gửi',
                        'type' => 'callback',
                        'value' => 'confirm_send'
                    ],
                    [
                        'text' => '✏️ Sửa lại',
                        'type' => 'callback',
                        'value' => 'edit_form'
                    ]
                ],
                [
                    [
                        'text' => '📋 Xem lại thông tin',
                        'type' => 'callback',
                        'value' => 'review_info'
                    ]
                ]
            ],
            'is_active' => true,
            'is_default' => true,
        ]);

        // Tạo các câu hỏi
        Question::create([
            'question_set_id' => $questionSet->id,
            'order' => 1,
            'question_text' => 'Bạn tên gì?',
            'field_name' => 'name',
            'validation_rule' => 'min:3',
            'error_message' => 'Tên phải có ít nhất 3 ký tự.',
            'is_required' => true,
        ]);

        Question::create([
            'question_set_id' => $questionSet->id,
            'order' => 2,
            'question_text' => 'Số điện thoại của bạn?',
            'field_name' => 'phone',
            'validation_rule' => 'phone',
            'error_message' => 'Số điện thoại không hợp lệ. Vui lòng nhập lại (ví dụ: 0912345678)',
            'is_required' => true,
        ]);

        Question::create([
            'question_set_id' => $questionSet->id,
            'order' => 3,
            'question_text' => 'Bạn muốn phản ánh vấn đề gì?',
            'field_name' => 'issue',
            'validation_rule' => 'min:10',
            'error_message' => 'Vui lòng mô tả vấn đề ít nhất 10 ký tự.',
            'is_required' => true,
        ]);
    }
}
