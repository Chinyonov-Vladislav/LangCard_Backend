<?php

namespace App\Http\Requests\Api\V1\TestRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StartTestRequest",
 *     title="Start Test Request (Начало теста)",
 *     description="Запрос для начала теста с указанием идентификатора теста.",
 *     type="object",
 *     required={"testId"},
 *
 *     @OA\Property(
 *         property="testId",
 *         type="integer",
 *         description="Идентификатор теста для запуска.",
 *         example=101
 *     )
 * )
 */
class StartTestRequest extends FormRequest
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
            'testId' => ['required', 'integer'],
        ];
    }
    public function messages(): array
    {
        return [
            'testId.required' => __('validation.testId_required'),
            'testId.integer' => __('validation.testId_int'),
        ];
    }
}
