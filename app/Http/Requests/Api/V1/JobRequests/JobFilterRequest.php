<?php

namespace App\Http\Requests\Api\V1\JobRequests;

use Illuminate\Foundation\Http\FormRequest;


/**
 * @OA\Schema(
 *     schema="JobFilterRequest",
 *     title="Job Filter Request (Параметры для пагинации и фильтрации записей job)",
 *     description="Query-параметры для получения записей о статусе выполненения запущенных job. Оба параметра являются необязательными и используются для пагинации.",
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
class JobFilterRequest extends FormRequest
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
            'page' => ['sometimes', 'integer', 'min:1'],
            'countOnPage' => ['sometimes', 'integer', 'min:1']
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
            'countOnPage.min' => 'Количество элементов на странице не может быть меньше 1.',
        ];
    }
}
