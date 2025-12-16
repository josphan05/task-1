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
        ];
    }
}

