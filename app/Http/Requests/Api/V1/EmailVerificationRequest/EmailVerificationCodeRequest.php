<?php

namespace App\Http\Requests\Api\V1\EmailVerificationRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="EmailVerificationCodeRequest",
 *     title = "Email Verification Code Request (Данные для подтверждения электронной почты)",
 *     type="object",
 *     required={"code"},
 *     description="Запрос на подтверждение email кодом",
 *     @OA\Property(
 *         property="code",
 *         type="string",
 *         description="Код подтверждения email, состоящий из ровно 6 цифр",
 *         example="123456",
 *         pattern="^\d{6}$"
 *     )
 * )
 */
class EmailVerificationCodeRequest extends FormRequest
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
            'code' => ['required', 'string', 'digits:6']
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Поле "Код" обязательно для заполнения.',
            'code.string' => 'Поле "Код" должно быть строкой.',
            'code.digits' => 'Поле "Код" должно содержать ровно 6 цифр.'
        ];
    }
}
