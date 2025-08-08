<?php

namespace App\Http\Requests\Api\V1\DeckRequests;

use Illuminate\Foundation\Http\FormRequest;

class AddingTopicsToDeckRequest extends FormRequest
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
            'deck_id'=> ['required', 'integer', 'exists:decks,id'],
            'topic_ids' => ['sometimes', 'array'],
            'topic_ids.*' => ['required', 'integer', 'exists:topics,id']
        ];
    }

    public function messages(): array
    {
        return [
            'deck_id.required' => 'Deck is required.',
            'deck_id.integer' => 'Deck must be an integer.',
            'deck_id.exists' => 'Deck does not exist.',
            'topic_ids.required' => 'Topics is required.',
            'topic_ids.array' => 'Topics must be an array.',
            'topic_ids.*.required' => 'Topics is required.',
            'topic_ids.*.integer' => 'Topics must be an integer.',
            'topic_ids.*.exists' => 'Topics does not exist.',
        ];
    }
}
