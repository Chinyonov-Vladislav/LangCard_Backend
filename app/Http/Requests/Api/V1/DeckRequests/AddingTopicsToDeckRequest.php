<?php

namespace App\Http\Requests\Api\V1\DeckRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="AddingTopicsToDeckRequest",
 *     title="Adding Topics To Deck Request (Добавление тем в колоду)",
 *     description="Данные для добавления одной или нескольких тем в существующую колоду",
 *     required={"topic_ids"},
 *
 *     @OA\Property(
 *         property="topic_ids",
 *         type="array",
 *         description="Список ID тем для добавления в колоду. Должен содержать хотя бы один элемент.",
 *         @OA\Items(type="integer", example=12)
 *     )
 * )
 */
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'topic_ids' => ['required', 'array'],
            'topic_ids.*' => ['required', 'integer', 'exists:topics,id']
        ];
    }

    public function messages(): array
    {
        return [
            'topic_ids.required' => 'Topics is required.',
            'topic_ids.array' => 'Topics must be an array.',
            'topic_ids.*.required' => 'Topics is required.',
            'topic_ids.*.integer' => 'Topics must be an integer.',
            'topic_ids.*.exists' => 'Topics does not exist.',
        ];
    }
}
