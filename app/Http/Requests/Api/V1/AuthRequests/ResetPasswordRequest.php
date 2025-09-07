<?php

namespace App\Http\Requests\Api\V1\AuthRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;


/**
 * @OA\Schema(
 *     schema="ResetPasswordRequest",
 *     title="Reset Password Request (Данные для сброса пароля неавторизованного пользователя)",
 *     description="Данные, необходимые для сброса пароля пользователя",
 *     required={"password", "password_confirmation", "token"},
 *
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         minLength=8,
 *         pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$",
 *         example="NewP@ssword2025"
 *     ),
 *     @OA\Property(
 *         property="password_confirmation",
 *         type="string",
 *         format="password",
 *         example="NewP@ssword2025"
 *     ),
 *     @OA\Property(
 *         property="token",
 *         type="string",
 *         example="eyJ0eXAiOiJKV1QiLCJh..."
 *     )
 * )
 */
class ResetPasswordRequest extends FormRequest
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
            'password' => ['required','string','min:8','regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'],
            'password_confirmation'=>['required', 'string', 'same:password'],
            'token' => ['required','string'],
        ];
    }
    public function messages(): array
    {
        return [
            'password.required' => __('validation.password_required'),
            'password.string' => __('validation.password_string'),
            'password.min'=>__('validation.password_min'),
            'password.regex' => __('validation.password_regex'),
            'password_confirmation.required' => "The field \"password_confirmation\" is required.",
            'password_confirmation.string' => "The field \"password_confirmation\" must be a string.",
            'password_confirmation.same' => "The field \"password_confirmation\" must be a same as field \"password\".",
            'token.required' => __('validation.token_required'),
            'token.string' => __('validation.token_string')
        ];
    }
}
