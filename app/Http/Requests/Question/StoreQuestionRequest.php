<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
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
            'question_text' => 'required|string',
            'field_name' => 'required|string|max:255',
            'order' => 'required|integer|min:1',
            'validation_rule' => 'nullable|string',
            'error_message' => 'nullable|string',
            'is_required' => 'boolean',
            'options' => 'nullable|string',
        ];
    }
}

