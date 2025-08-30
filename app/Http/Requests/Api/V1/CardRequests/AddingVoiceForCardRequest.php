<?php

namespace App\Http\Requests\Api\V1\CardRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="AddingVoiceForCardRequest",
 *     title="Adding Voice For Card Request (Добавление голосов для карточки)",
 *     description="Данные для добавления голосов произношения к существующей карточке",
 *     @OA\Property(
 *         property="originalVoices",
 *         type="array",
 *         description="Массив ID голосов для оригинального слова. Каждый ID должен существовать в таблице voices (поле voice_id).",
 *         @OA\Items(
 *             type="string",
 *             example="en_male_1"
 *         )
 *     ),
 *     @OA\Property(
 *         property="targetVoices",
 *         type="array",
 *         description="Массив ID голосов для перевода слова. Каждый ID должен существовать в таблице voices (поле voice_id).",
 *         @OA\Items(
 *             type="string",
 *             example="ru_female_2"
 *         )
 *     )
 * )
 */
class AddingVoiceForCardRequest extends FormRequest
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
            'originalVoices' => ['sometimes','array'],
            'originalVoices.*' => ['required', 'string', 'exists:voices,voice_id'],
            'targetVoices' => ['sometimes','array'],
            'targetVoices.*' => ['required','string', 'exists:voices,voice_id'],
        ];
    }

    public function messages(): array
    {
        return [
            'originalVoices.array' => 'Поле "Оригинальные голоса" должно быть массивом.',
            'originalVoices.*.required' => 'Каждый элемент в "Оригинальные голоса" обязателен.',
            'originalVoices.*.string' => 'Каждый элемент в "Оригинальные голоса" должен быть строкой.',
            'originalVoices.*.exists' => 'Выбранный оригинальный голос не существует.',

            'targetVoices.array' => 'Поле "Целевые голоса" должно быть массивом.',
            'targetVoices.*.required' => 'Каждый элемент в "Целевые голоса" обязателен.',
            'targetVoices.*.string' => 'Каждый элемент в "Целевые голоса" должен быть строкой.',
            'targetVoices.*.exists' => 'Выбранный целевой голос не существует.',
        ];
    }
}
