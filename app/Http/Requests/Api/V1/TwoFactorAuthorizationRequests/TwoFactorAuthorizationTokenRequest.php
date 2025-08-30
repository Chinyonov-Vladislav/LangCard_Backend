<?php

namespace App\Http\Requests\Api\V1\TwoFactorAuthorizationRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="TwoFactorAuthorizationTokenRequest",
 *     title="TwoFactorAuthorizationTokenRequest (Запрос токена двухфакторной аутентификации для отправки электронного письма с кодом для авторизации)",
 *     description="Запрос токена двухфакторной аутентификации для отправки электронного письма с кодом для авторизации",
 *     type="object",
 *     required={"token"},
 *
 *     @OA\Property(
 *         property="token",
 *         type="string",
 *         description="Токен для двухфакторной аутентификации.",
 *         example="abcdef123456"
 *     )
 * )
 */
class TwoFactorAuthorizationTokenRequest extends FormRequest
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
        ];
    }
    public function messages(): array
    {
        return [
            'token.required' => 'Token is required',
            'token.string' => 'Token must be string',
        ];
    }
}
