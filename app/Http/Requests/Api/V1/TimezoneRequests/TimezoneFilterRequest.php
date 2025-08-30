<?php

namespace App\Http\Requests\Api\V1\TimezoneRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;


/**
 * @OA\Schema(
 *     schema="TimezoneFilterRequest",
 *     type="object",
 *     title="Timezone Filter Request (Параметры фильтрации часовых поясов), которые передаются через query string",
 *     description="Параметры фильтрации часовых поясов",
 *     @OA\Property(
 *         property="page",
 *         type="integer",
 *         minimum=1,
 *         description="Номер страницы (необязательный параметр)"
 *     ),
 *     @OA\Property(
 *         property="countOnPage",
 *         type="integer",
 *         minimum=1,
 *         description="Количество элементов на странице (необязательный параметр)"
 *     ),
 *     @OA\Property(
 *         property="fields",
 *         type="string",
 *         description="Поля, которые нужно вернуть (необязательный параметр)"
 *     )
 * )
 */
class TimezoneFilterRequest extends FormRequest
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
            'page' => ['sometimes', 'integer', 'min:1'],
            'countOnPage' => ['sometimes', 'integer', 'min:1'],
            'fields' => ['sometimes', 'string'],
        ];
    }
    public function messages(): array
    {
        return [
            'page.integer' => 'Page must be an integer.',
            'page.min' => 'Page must be a positive integer.',
            'countOnPage.integer' => 'Count must be an integer.',
            'countOnPage.min' => 'Count must be a positive integer.',
            'fields.string' => 'Fields must be strings.',
        ];
    }
}
