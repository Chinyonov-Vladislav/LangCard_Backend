<?php

namespace App\Http\Requests\Api\V1\UpdatePasswordRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdatePasswordRequest",
 *     title="Update Password Request Auth User (Данные для обновления пароля авторизованного пользователя )",
 *     description="Данные для запроса для обновления пароля авторизованного пользователя",
 *     type="object",
 *     required={"password", "password_confirmation"},
 *
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         description="Новый пароль, минимум 8 символов, должен содержать заглавные и строчные буквы, цифры и специальные символы.",
 *         minLength=8,
 *         pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$",
 *         example="StrongPass1!"
 *     ),
 *     @OA\Property(
 *         property="password_confirmation",
 *         type="string",
 *         description="Подтверждение пароля, должно совпадать с полем password.",
 *         example="StrongPass1!"
 *     )
 * )
 */
class UpdatePasswordRequest extends FormRequest
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
            'password' => ['required', 'string', 'confirmed', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/']
        ];
    }
    public function messages(): array
    {
        return [
            'password.required' => __('validation.password_required'),
            'password.confirmed' => __('validation.password_confirmed'),
            'password.min'=>__('validation.password_min'),
            'password.regex' => __('validation.password_regex'),
        ];
    }
}
