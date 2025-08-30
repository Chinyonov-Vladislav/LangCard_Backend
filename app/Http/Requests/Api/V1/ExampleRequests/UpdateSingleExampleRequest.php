<?php

namespace App\Http\Requests\Api\V1\ExampleRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;


/**
 * @OA\Schema(
 *     schema="UpdateSingleExampleRequest",
 *     title="Update Single Example Request (Обновление одного примера)",
 *     description="Данные для обновления одного примера использования слова в карточке",
 *     required={"example_id","example","source"},
 *
 *     @OA\Property(
 *         property="example_id",
 *         type="integer",
 *         description="ID примера, который нужно обновить",
 *         example=123
 *     ),
 *
 *     @OA\Property(
 *         property="example",
 *         type="string",
 *         maxLength=255,
 *         description="Текст примера (не более 255 символов)",
 *         example="I like to eat apples"
 *     ),
 *
 *     @OA\Property(
 *         property="source",
 *         type="string",
 *         enum={"original","target"},
 *         description="Источник примера: original — для оригинального слова, target — для перевода",
 *         example="original"
 *     )
 * )
 */
class UpdateSingleExampleRequest extends FormRequest
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
            'example' => ['required', 'string', 'max:255'],
            'source'=>['required', 'string', 'in:original,target']
        ];
    }

    public function messages(): array
    {
        return [
            'example.required' => 'Поле "example" обязательно для заполнения.',
            'example.string' => 'Поле "example" должно быть строкой.',
            'example.max' => 'Поле "example" не должно превышать 255 символов.',

            'source.required' => 'Поле "source" обязательно для заполнения.',
            'source.string' => 'Поле "source" должно быть строкой.',
            'source.in' => 'Поле "source" должно принимать значения "original" или "target".',
        ];
    }
}
