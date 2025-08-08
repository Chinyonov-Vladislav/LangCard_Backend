<?php

namespace App\Http\Requests\Api\V1\TopicRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatingTopicRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'topic_id'=> ['required', 'integer', 'exists:topics,id'],
            'name'=>['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'topic_id.required' => 'Topic is required.',
            'topic_id.integer' => 'Topic must be an integer.',
            'topic_id.exists' => 'Topic does not exist.',
            'name.required' => 'Topic name is required.',
            'name.string' => 'Topic name must be a string.',
            'name.max' => 'Topic name cannot be longer than 255 characters.',
        ];
    }
}
