<?php

namespace App\Http\Requests\Api\V1\CheckCorrectSentenceRequests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="CheckCorrectSentenceRequest",
 *     title="Check Correct Sentence Request (Проверка правильности предложения)",
 *     description="Данные для проверки грамматической корректности текста",
 *     required={"language", "text"},
 *
 *     @OA\Property(
 *         property="language",
 *         type="string",
 *         example="en",
 *         description="Код языка (например: en, ru, fr)"
 *     ),
 *     @OA\Property(
 *         property="text",
 *         type="string",
 *         example="This is a test sentence.",
 *         description="Предложение для проверки"
 *     )
 * )
 */
class CheckCorrectSentenceRequest extends FormRequest
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
            'language'=>['required','string'],
            'text'=>['required','string'],
        ];
    }
    public function messages(): array
    {
        return [
            'language.required' => __('validation.language_required'),
            'language.string' => __('validation.language_string'),
            'text.required' => __('validation.text_required'),
            'text.string' => __('validation.text_string'),
        ];
    }
}
