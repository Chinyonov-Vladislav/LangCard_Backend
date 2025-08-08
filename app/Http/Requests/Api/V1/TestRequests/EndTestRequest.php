<?php

namespace App\Http\Requests\Api\V1\TestRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="EndTestRequest",
 *     title="End Test Request (Завершение теста)",
 *     description="Запрос для завершения теста с идентификатором попытки, флагом автоматического завершения и массивом ответов.",
 *     type="object",
 *     required={"attemptId", "automatic", "answers"},
 *
 *     @OA\Property(
 *         property="attemptId",
 *         type="integer",
 *         description="Идентификатор попытки теста.",
 *         example=123
 *     ),
 *
 *     @OA\Property(
 *         property="answers",
 *         type="array",
 *         description="Массив ответов на вопросы теста.",
 *         @OA\Items(
 *             type="object",
 *             required={"question_id"},
 *             @OA\Property(
 *                 property="question_id",
 *                 type="integer",
 *                 description="Идентификатор вопроса.",
 *                 example=10
 *             ),
 *             @OA\Property(
 *                 property="answer_id",
 *                 type="integer",
 *                 nullable=true,
 *                 description="Идентификатор выбранного ответа. Может быть null.",
 *                 example=5
 *             )
 *         )
 *     )
 * )
 */
class EndTestRequest extends FormRequest
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
            'attemptId' => ['required', 'integer', 'exists:user_test_results,id'],
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'integer'],
            'answers.*.answer_id' => ['nullable', 'integer'],
        ];
    }
    public function messages(): array
    {
        return [
            'attemptId.required' => __('validation.attemptId_required'),
            'attemptId.integer' => __('validation.attemptId_int'),
            'attemptId.exists' => 'Attempt does not exist',
            'answers.required' => __('validation.answers_required'),
            'answers.array' => __('validation.answers_array'),
            'answers.*.question_id.required' => __('validation.answers_item_question_id_required'),
            'answers.*.question_id.integer' => __('validation.answers_item_question_id_int'),
            'answers.*.answer_id.integer' => __('validation.answers_item_answer_id_int'),
        ];
    }
}
