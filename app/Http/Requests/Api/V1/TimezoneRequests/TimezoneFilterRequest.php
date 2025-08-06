<?php

namespace App\Http\Requests\Api\V1\TimezoneRequests;

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
 *         nullable = true,
 *         description="Номер страницы"
 *     ),
 *     @OA\Property(
 *         property="countOnPage",
 *         type="integer",
 *         minimum=1,
 *         nullable = true,
 *         description="Количество элементов на странице"
 *     ),
 *     @OA\Property(
 *         property="fields",
 *         type="string",
 *         nullable=true,
 *         description="Поля, которые нужно вернуть"
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'countOnPage' => ['nullable', 'integer', 'min:1'],
            'fields' => ['nullable', 'string'],
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
