<?php

namespace App\Http\Requests\Api\V1\TwoFactorAuthorizationRequests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="CodeGoogleAuthenticatorRequest",
 *     title="Code Google Authenticator Request (Данные для подтверждения Google Authenticator)",
 *     description="Данные для подтверждения двухфакторной аутентификации с поомощью Google Authenticator.",
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
 *         description="Код Google Authenticator.",
 *         example="123456"
 *     )
 * )
 */
class CodeGoogleAuthenticatorRequest extends FormRequest
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
            'token' => ['required', 'string'],
            'code' => ['required', 'string']
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Token is required.',
            'token.string' => 'Token must be a string.',
            'code.required' => 'The code is required.',
            'code.string' => 'The code must be a string.',
        ];
    }
}
