<?php

namespace App\Http\Requests\Api\V1\TestRequests;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'testId' => ['required', 'int'],
        ];
    }
    public function messages(): array
    {
        return [
            'testId.required' => __('validation.testId_required'),
            'testId.int' => __('validation.testId_int'),
        ];
    }
}
