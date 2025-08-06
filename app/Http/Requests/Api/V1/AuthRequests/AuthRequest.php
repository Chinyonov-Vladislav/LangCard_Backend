<?php

namespace App\Http\Requests\Api\V1\AuthRequests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="AuthRequest",
 *     required={"email", "password"},
 *     title="Auth Request (Данные для авторизации)",
 *     description="Данные, необходимые для авторизации пользователя",
 *     @OA\Property(property="email", type="string", format="email", maxLength=255, example="user@example.com"),
 *     @OA\Property(property="password", type="string", minLength=8, example="password123")
 * )
 */
class AuthRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:255', 'exists:users,email'],
            'password' => ['required', 'string'],
        ];
    }
    public function messages(): array
    {
        return [
            'email.required' => __('validation.email_required'),
            'email.string' => __('validation.email_string'),
            'email.email' => __('validation.email_email'),
            'email.max' => __('validation.email_max'),
            'email.exists' => __('validation.email_exists'),
            'password.required' => __('validation.password_required'),
            'password.string' => __('validation.password_string')
        ];
    }
}
