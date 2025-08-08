<?php

namespace App\Http\Requests\Api\V1\TestRequests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="GetQuestionsForTestRequest",
 *     title="Get Questions For Test Request (Получение вопросов для теста)",
 *     description="Запрос для получения списка вопросов по ID попытки прохождения теста.",
 *     type="object",
 *     required={"attemptId"},
 *
 *     @OA\Property(
 *         property="attemptId",
 *         type="integer",
 *         description="Идентификатор попытки прохождения теста.",
 *         example=125
 *     )
 * )
 */
class GetQuestionsForTestRequest extends FormRequest
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
            'attemptId' => ['required','integer','exists:user_test_results,id'],
        ];
    }
    public function messages(): array
    {
        return [
            'attemptId.required' => 'Attempt is required',
            'attemptId.integer' => 'Attempt must be an integer',
            'attemptId.exists' => 'Attempt does not exist',
        ];
    }
}
