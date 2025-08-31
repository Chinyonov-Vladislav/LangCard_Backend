<?php

namespace App\Http\Requests\Api\V1\AuthRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;


/**
 * @OA\Schema(
 *     schema="PasswordResetTokenRequest",
 *     required={"token"},
 *     title="Password Reset Token Request (Данные для проверки коррректности токена сброса пароля)",
 *     description="Данные, необходимые для проверки коррректности токена сброса пароля",
 *     @OA\Property(property="token", type="string", example="bd65600d-8669-4903-8a14-af88203add38"),
 * )
 */
class PasswordResetTokenRequest extends FormRequest
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
            'token' => ['required','string']
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => "The field 'token' is required.",
            "token.string" => "The field 'token' must be a string.",
        ];
    }
}
