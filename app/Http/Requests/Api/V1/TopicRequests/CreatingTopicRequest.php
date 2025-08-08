<?php

namespace App\Http\Requests\Api\V1\TopicRequests;

use Illuminate\Foundation\Http\FormRequest;

class CreatingTopicRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Topic name is required.',
            'name.string' => 'Topic name must be a string.',
            'name.max' => 'Topic name cannot be longer than 255 characters.',
        ];
    }
}
