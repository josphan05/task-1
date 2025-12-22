<?php

namespace App\Http\Requests\QuestionSet;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionSetRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_message' => 'nullable|string',
            'completion_message' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}

