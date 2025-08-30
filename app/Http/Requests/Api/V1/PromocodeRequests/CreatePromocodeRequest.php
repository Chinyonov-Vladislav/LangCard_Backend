<?php

namespace App\Http\Requests\Api\V1\PromocodeRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="CreatePromocodeRequest",
 *     title="Create Promocode Request (Создание промокодов)",
 *     description="Схема для создания одного или нескольких промокодов. Требуется указать количество создаваемых кодов (целое положительное число).",
 *     type="object",
 *     required={"count"},
 *
 *     @OA\Property(
 *         property="count",
 *         type="integer",
 *         format="int32",
 *         description="Количество создаваемых промокодов. Целое положительное число, минимум 1.",
 *         example=10,
 *         minimum=1
 *     )
 * )
 */
class CreatePromocodeRequest extends FormRequest
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
            'count' => ['required', 'int', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'count.required' => 'Поле \'count\' обязательно для заполнения',
            'count.int' => 'Поле \'count\' должно быть целым числом',
            'count.min' => 'Поле \'count\' должно быть положительным числом (минимальное значение = 1)',
        ];
    }
}
