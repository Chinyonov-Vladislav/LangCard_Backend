<?php

namespace App\Http\Requests\Api\V1\ExampleRequests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="AddingMultipleExamplesRequest",
 *     title="Adding Multiple Examples Request (Добавление нескольких примеров)",
 *     description="Данные для добавления нескольких примеров в карточку",
 *     required={"examples"},
 *
 *     @OA\Property(
 *         property="examples",
 *         type="array",
 *         description="Массив примеров для добавления",
 *         @OA\Items(
 *             type="object",
 *             required={"example","source"},
 *
 *             @OA\Property(
 *                 property="example",
 *                 type="string",
 *                 maxLength=255,
 *                 example="I like to eat apples",
 *                 description="Текст примера (не более 255 символов)"
 *             ),
 *
 *             @OA\Property(
 *                 property="source",
 *                 type="string",
 *                 enum={"original","target"},
 *                 example="original",
 *                 description="Источник примера: original — для оригинального слова, target — для перевода"
 *             )
 *         )
 *     )
 * )
 */
class AddingMultipleExamplesRequest extends FormRequest
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
            'examples.*.example' => ['required', 'string', 'max:255'],
            'examples.*.source' => ['required', 'string', 'in:original,target'],
        ];
    }
    public function messages(): array
    {
        return [

            'examples.required' => 'Массив примеров обязателен.',
            'examples.array' => 'Поле примеров должно быть массивом.',

            'examples.*.example.required' => 'Поле "example" в каждом примере обязательно.',
            'examples.*.example.string' => 'Поле "example" должно быть строкой.',
            'examples.*.example.max' => 'Поле "example" не должно превышать 255 символов.',

            'examples.*.source.required' => 'Поле "source" в каждом примере обязательно.',
            'examples.*.source.string' => 'Поле "source" должно быть строкой.',
            'examples.*.source.in' => 'Поле "source" должно иметь значение "original" или "target".',
        ];
    }
}
