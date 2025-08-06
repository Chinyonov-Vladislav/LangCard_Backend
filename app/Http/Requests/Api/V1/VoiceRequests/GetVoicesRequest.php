<?php

namespace App\Http\Requests\Api\V1\VoiceRequests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="GetVoicesRequest",
 *     title="Get Voices Request Query Params (Query параметры запроса получения данных о поддерживаемых голосах в системе)",
 *     description="Query-параметры для получения данных о поддерживаемых голосах в системе. Оба параметра являются необязательными и используются для пагинации.",
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
class GetVoicesRequest extends FormRequest
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
            'page' => ['sometimes', 'integer', 'min:1',],
            'countOnPage' => ['sometimes','integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            // Валидация страницы
            'page.integer' => 'Номер страницы должен быть целым числом.',
            'page.min' => 'Номер страницы не может быть меньше 1.',

            // Валидация количества элементов на странице
            'countOnPage.integer' => 'Количество элементов на странице должно быть целым числом.',
            'countOnPage.min' => 'Количество элементов на странице не может быть меньше 1.'
        ];
    }
}
