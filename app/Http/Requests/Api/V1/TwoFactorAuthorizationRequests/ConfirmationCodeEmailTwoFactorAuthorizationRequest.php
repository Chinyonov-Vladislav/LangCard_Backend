<?php

namespace App\Http\Requests\Api\V1\TwoFactorAuthorizationRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ConfirmationCodeEmailTwoFactorAuthorizationRequest",
 *     title="Confirmation Code Email Two Factor Authorization Request (Подтверждение двухфакторной аутентификации по Email)",
 *     description="Данные для подтверждения двухфакторной аутентификации по Email.",
 *     type="object",
 *     required={"token", "code"},
 *
 *     @OA\Property(
 *         property="token",
 *         type="string",
 *         description="Токен для верификации пользователя.",
 *         example="abcdef123456"
 *     ),
 *
 *     @OA\Property(
 *         property="code",
 *         type="string",
 *         description="Код подтверждения из email, ровно 6 символов.",
 *         minLength=6,
 *         maxLength=6,
 *         example="123456"
 *     )
 * )
 */
class ConfirmationCodeEmailTwoFactorAuthorizationRequest extends FormRequest
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
            'token' => ['required', 'string'],
            'code' => ['required', 'string', 'size:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Token is required.',
            'token.string' => 'Token must be a string.',

            // Код
            'code.required' => 'Поле кода обязательно для заполнения.',
            'code.string' => 'Поле кода должно быть строкой.',
            'code.size' => 'Код должен состоять ровно из 6 символов.',
        ];
    }
}
