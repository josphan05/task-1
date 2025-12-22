<?php

namespace App\Http\Requests\QuestionSetCommand;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionSetCommandRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'command' => 'required|string|max:255|unique:question_set_commands,command',
            'question_set_id' => 'required|exists:question_sets,id',
            'response_message' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}

