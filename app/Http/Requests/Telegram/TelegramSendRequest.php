<?php

namespace App\Http\Requests\Telegram;

use Illuminate\Foundation\Http\FormRequest;

class TelegramSendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_type' => ['required', 'in:users,chatgroup'],
            'user_ids' => ['required_if:target_type,users', 'array', 'min:1'],
            'user_ids.*' => ['exists:users,id'],
            'message' => ['required', 'string', 'min:1', 'max:4096'],

            // Inline keyboard buttons validation
            'buttons' => ['nullable', 'array', 'max:10'], // Max 10 rows
            'buttons.*' => ['array', 'max:8'], // Max 8 buttons per row
            'buttons.*.*' => ['array'],
            'buttons.*.*.text' => ['required_with:buttons.*.*.value', 'string', 'max:64'],
            'buttons.*.*.type' => ['required_with:buttons.*.*.text', 'in:url,callback'],
            'buttons.*.*.value' => ['required_with:buttons.*.*.text', 'string', 'max:256'],
        ];
    }

    public function messages(): array
    {
        return [
            'target_type.required' => 'Vui lòng chọn kiểu gửi.',
            'target_type.in' => 'Kiểu gửi không hợp lệ.',
            'user_ids.required_if' => 'Vui lòng chọn ít nhất một người dùng.',
            'user_ids.min' => 'Vui lòng chọn ít nhất một người dùng.',
            'message.required' => 'Vui lòng nhập nội dung tin nhắn.',
            'message.max' => 'Tin nhắn không được vượt quá 4096 ký tự.',

            // Button validation messages
            'buttons.max' => 'Tối đa 10 hàng nút.',
            'buttons.*.max' => 'Mỗi hàng tối đa 8 nút.',
            'buttons.*.*.text.required_with' => 'Vui lòng nhập tên nút.',
            'buttons.*.*.text.max' => 'Tên nút không được vượt quá 64 ký tự.',
            'buttons.*.*.type.required_with' => 'Vui lòng chọn loại nút.',
            'buttons.*.*.type.in' => 'Loại nút không hợp lệ.',
            'buttons.*.*.value.required_with' => 'Vui lòng nhập giá trị nút.',
            'buttons.*.*.value.max' => 'Giá trị nút không được vượt quá 256 ký tự.',
        ];
    }
}

