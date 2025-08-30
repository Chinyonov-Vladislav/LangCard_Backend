<?php

namespace App\Http\Requests\Api\V1\TwoFactorAuthorizationRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UseRecoveryCodeRequest",
 *     title="Use Recovery Code Request (Данные на использование кода восстановления)",
 *     description="Данные для запроса с токеном и кодом восстановления для двухфакторной аутентификации.",
 *     type="object",
 *     required={"token", "recovery_code"},
 *
 *     @OA\Property(
 *         property="token",
 *         type="string",
 *         description="Токен двухфакторной аутентификации.",
 *         example="abcdef123456"
 *     ),
 *     @OA\Property(
 *         property="recovery_code",
 *         type="string",
 *         description="Код восстановления длиной ровно 8 символов.",
 *         minLength=8,
 *         maxLength=8,
 *         example="1234ABCD"
 *     )
 * )
 */
class UseRecoveryCodeRequest extends FormRequest
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
            'recovery_code' => ['required', 'string', 'size:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Token is required',
            'token.string' => 'Token must be a string',
            'recovery_code.required' => 'Recovery code is required',
            'recovery_code.string' => 'Recovery code must be a string',
            'recovery_code.size' => 'The length of the recovery code must be 8',
        ];
    }
}
