<?php

namespace App\Http\Requests\Api\V1\ExampleRequests;

use Illuminate\Foundation\Http\FormRequest;


/**
 * @OA\Schema(
 *     schema="UpdateMultipleExamplesRequest",
 *     title="Update Multiple Examples Request (Массовое обновление примеров)",
 *     description="Данные для массового обновления примеров использования слова в карточке",
 *     required={"examples"},
 *
 *     @OA\Property(
 *         property="examples",
 *         type="array",
 *         description="Массив примеров для обновления",
 *         @OA\Items(
 *             @OA\Property(
 *                 property="id",
 *                 type="integer",
 *                 description="ID примера, который нужно обновить",
 *                 example=123
 *             ),
 *             @OA\Property(
 *                 property="example",
 *                 type="string",
 *                 maxLength=255,
 *                 description="Текст примера (не более 255 символов)",
 *                 example="I like to eat apples"
 *             ),
 *             @OA\Property(
 *                 property="source",
 *                 type="string",
 *                 enum={"original","target"},
 *                 description="Источник примера: original — для оригинального слова, target — для перевода",
 *                 example="original"
 *             )
 *         )
 *     )
 * )
 */
class UpdateMultipleExamplesRequest extends FormRequest
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
            'examples' => ['required','array'],
            'examples.*.id' => ['required','integer', 'exists:examples,id'],
            'examples.*.example' => ['required', 'string', 'max:255'],
            'examples.*.source' => ['required', 'string', 'in:original,target']
        ];
    }
    public function messages(): array
    {
        return [
            'examples.required' => 'Поле "examples" обязательно для заполнения.',
            'examples.array' => 'Поле "examples" должно быть массивом.',

            'examples.*.id.required' => 'Идентификатор примера обязателен.',
            'examples.*.id.integer' => 'Идентификатор примера должен быть числом.',
            'examples.*.id.exists' => 'Указанный пример не найден.',

            'examples.*.example.required' => 'Поле "example" обязательно для заполнения.',
            'examples.*.example.string' => 'Поле "example" должно быть строкой.',
            'examples.*.example.max' => 'Поле "example" не должно превышать 255 символов.',

            'examples.*.source.required' => 'Поле "source" обязательно для заполнения.',
            'examples.*.source.string' => 'Поле "source" должно быть строкой.',
            'examples.*.source.in' => 'Поле "source" должно принимать значения "original" или "target".',
        ];
    }
}
