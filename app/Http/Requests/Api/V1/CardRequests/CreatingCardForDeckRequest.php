<?php

namespace App\Http\Requests\Api\V1\CardRequests;

use App\Rules\ImagePathExistsRule;
use App\Rules\IsFileBelongsToImagesRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="CreatingCardForDeckRequest",
 *     title="Creating Card For Deck Request (Создание карточки в колоде)",
 *     description="Данные, небходимые для создания новой карточки для колоды",
 *     required={"deck_id", "word", "translate"},
 *
 *     @OA\Property(
 *         property="deck_id",
 *         type="integer",
 *         example=12,
 *         description="ID существующей колоды"
 *     ),
 *     @OA\Property(
 *         property="word",
 *         type="string",
 *         maxLength=255,
 *         example="apple",
 *         description="Оригинальное слово"
 *     ),
 *     @OA\Property(
 *         property="translate",
 *         type="string",
 *         maxLength=255,
 *         example="яблоко",
 *         description="Перевод слова"
 *     ),
 *     @OA\Property(
 *         property="imagePath",
 *         type="string",
 *         nullable=true,
 *         example="/images/cards/apple.jpg",
 *         description="Путь к изображению (если передаётся)"
 *     )
 * )
 */
class CreatingCardForDeckRequest extends FormRequest
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
            'deck_id' => ['required', 'integer', 'exists:decks,id'],
            'word' => ['required', 'string', 'max:255'],
            'translate' => ['required', 'string', 'max:255'],
            'imagePath' => [
                'nullable',
                'string',
                new ImagePathExistsRule(),
                new IsFileBelongsToImagesRule()
            ],

        ];
    }
    public function messages(): array
    {
        return [
            'deck_id.required' => 'Поле "Колода" обязательно для заполнения.',
            'deck_id.integer' => 'Поле "Колода" должно быть числом.',
            'deck_id.exists' => 'Выбранная колода не существует.',
            'word.required' => 'Поле "Слово" обязательно для заполнения.',
            'word.string' => 'Поле "Слово" должно быть строкой.',
            'word.max' => 'Поле "Слово" не может содержать более 255 символов.',
            'translate.required' => 'Поле "Перевод" обязательно для заполнения.',
            'translate.string' => 'Поле "Перевод" должно быть строкой.',
            'translate.max' => 'Поле "Перевод" не может содержать более 255 символов.',
            'imagePath.string' => 'Путь к изображению должен быть строкой.',
        ];
    }
}
