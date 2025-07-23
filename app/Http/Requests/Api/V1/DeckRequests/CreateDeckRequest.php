<?php

namespace App\Http\Requests\Api\V1\DeckRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateDeckRequest extends FormRequest
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
            'name'=>['required','string','max:255'],
            'original_language_id' => ['required','exists:languages,id'],
            'target_language_id' => ['required','exists:languages,id','different:original_language_id'],
            'is_premium' => ['boolean'],
            'topic_ids' => ['array'],
            'topic_ids.*' => 'exists:topics,id'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Deck name is required.',
            'name.max' => 'Deck name cannot exceed 255 characters.',
            'target_language_id.different' => 'Target language must be different from original language.',
            'original_language_id.exists' => 'Selected original language does not exist.',
            'target_language_id.exists' => 'Selected target language does not exist.',
            'topic_ids.array' => 'Topics must be provided as an array.',
            'topic_ids.max' => 'You can select maximum 10 topics.',
            'topic_ids.*.exists' => 'One or more selected topics do not exist.',
        ];
    }
}
