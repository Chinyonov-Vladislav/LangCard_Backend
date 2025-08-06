<?php

namespace App\Http\Requests\Api\V1\TariffRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="AddingNewTariffRequest",
 *     title="Adding New Tariff Request (Добавление нового тарифа)",
 *     description="Схема запроса для создания нового тарифа с уникальным количеством дней и названием.",
 *     type="object",
 *     required={"name", "days"},
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         description="Название тарифа.",
 *         example="Стандартный тариф"
 *     ),
 *     @OA\Property(
 *         property="days",
 *         type="integer",
 *         minimum=1,
 *         description="Количество дней тарифа. Должно быть уникальным в базе данных.",
 *         example=30
 *     )
 * )
 */
class AddingNewTariffRequest extends FormRequest
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
            'days' => ['required', 'integer', 'min:1', Rule::unique('tariffs', 'days')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.name_required'),
            'name.string' => __('validation.name_string'),
            'name.max' => __('validation.name_max'),

            'days.required' => __('validation.days_required'),
            'days.integer' => __('validation.days_integer'),
            'days.min' => __('validation.days_min'),
            'days.unique' => __('validation.days_unique')
        ];
    }
}
