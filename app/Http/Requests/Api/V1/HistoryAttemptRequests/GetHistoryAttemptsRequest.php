<?php

namespace App\Http\Requests\Api\V1\HistoryAttemptRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="GetHistoryAttemptsRequestQueryParams",
 *     title="Get History Attempts Request Query Params (Параметры запроса истории попыток прохождения тестов)",
 *     description="Query-параметры для получения истории попыток. Оба параметра являются необязательными и используются для пагинации.",
 *     type="object",
 *     @OA\Property(
 *         property="page",
 *         type="integer",
 *         minimum=1,
 *         example=1,
 *         description="Номер страницы"
 *     ),
 *     @OA\Property(
 *         property="countOnPage",
 *         type="integer",
 *         minimum=1,
 *         example=10,
 *         description="Количество элементов на странице"
 *     )
 * )
 */
class GetHistoryAttemptsRequest extends FormRequest
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
            'page' => [
                'nullable',
                'integer',
                'min:1'
            ],
            'countOnPage' => [
                'nullable',
                'integer',
                'min:1'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'page.integer' => 'Номер страницы должен быть целым числом.',
            'page.min' => 'Номер страницы не может быть меньше 1.',
            'countOnPage.integer' => 'Количество элементов на странице должно быть целым числом.',
            'countOnPage.min' => 'Количество элементов на странице не может быть меньше 1.',
        ];
    }
}
