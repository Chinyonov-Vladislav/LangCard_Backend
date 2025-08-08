<?php

namespace App\Http\Requests\Api\V1\DeckRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="CreateDeckRequest",
 *     title="Create Deck Request (Создание колоды)",
 *     description="Данные для создания новой языковой колоды",
 *     required={"name", "original_language_id", "target_language_id"},
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         example="Фрукты и овощи",
 *         description="Название колоды"
 *     ),
 *     @OA\Property(
 *         property="original_language_id",
 *         type="integer",
 *         example=1,
 *         description="ID оригинального языка (например, английский)"
 *     ),
 *     @OA\Property(
 *         property="target_language_id",
 *         type="integer",
 *         example=2,
 *         description="ID целевого языка (например, испанский); должен отличаться от оригинального"
 *     ),
 *     @OA\Property(
 *         property="is_premium",
 *         type="boolean",
 *         example=false,
 *         description="Флаг премиум-доступа к колоде"
 *     )
 * )
 */
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
            'name' => ['required', 'string', 'max:255'],
            'original_language_id' => ['required', 'exists:languages,id'],
            'target_language_id' => ['required', 'exists:languages,id', 'different:original_language_id'],
            'is_premium' => ['required', 'boolean'],

        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Название обязательно для заполнения.',
            'name.string' => 'Название должно быть строкой.',
            'name.max' => 'Название не должно превышать 255 символов.',

            'original_language_id.required' => 'Необходимо указать язык оригинала.',
            'original_language_id.exists' => 'Указанный язык оригинала не найден.',

            'target_language_id.required' => 'Необходимо указать язык перевода.',
            'target_language_id.exists' => 'Указанный язык перевода не найден.',
            'target_language_id.different' => 'Язык перевода должен отличаться от языка оригинала.',

            'is_premium.required' => 'Необходимо указать, является ли колода премиумной.',
            'is_premium.boolean' => 'Поле "is_premium" должно быть булевым значением (true/false).',

            'topic_ids.array' => 'Поле "topic_ids" должно быть массивом.',

            'topic_ids.*.required' => 'Каждый элемент в "topic_ids" обязателен.',
            'topic_ids.*.integer' => 'Каждый элемент в "topic_ids" должен быть целым числом.',
            'topic_ids.*.exists' => 'Некоторые темы в "topic_ids" не найдены.',
        ];
    }
}
