<?php

namespace App\Http\Requests\Api\V1\AuthRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
/**
 * @OA\Schema(
 *     schema="RegistrationRequest",
 *     required={"name", "email", "password", "password_confirmation", "mailing_enabled", "terms_accepted"},
 *     title="Registration Request (Данные для регистрации)",
 *     description="Данные, необходимые для регистрации нового пользователя",
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         example="Иван Иванов"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         maxLength=255,
 *         example="ivan@example.com"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         minLength=8,
 *         pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$",
 *         example="Str0ngP@ssword!"
 *     ),
 *     @OA\Property(
 *         property="password_confirmation",
 *         type="string",
 *         format="password",
 *         example="Str0ngP@ssword!"
 *     ),
 *     @OA\Property(
 *          property="mailing_enabled",
 *          type="boolean",
 *          example="true"
 *      ),
 *      @OA\Property(
 *           property="terms_accepted",
 *           type="boolean",
 *           example="true"
 *       )
 * )
 */
class RegistrationRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'],
            'password_confirmation'=>['required', 'string', 'same:password'],
            'mailing_enabled'=>['required','boolean'],
            'terms_accepted'=>["required","boolean"],
        ];
    }
    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('validation.name_required'),
            'name.string' => __('validation.name_string'),
            'name.max' => __('validation.name_max'),
            'email.required' => __('validation.email_required'),
            'email.unique' => __('validation.email_unique'),
            'password.required' => __('validation.password_required'),
            'password.min'=>__('validation.password_min'),
            'password.regex' => __('validation.password_regex'),
            'password_confirmation.required' => "The field \"password_confirmation\" is required.",
            'password_confirmation.string' => "The field \"password_confirmation\" must be a string.",
            'password_confirmation.same' => "The field \"password_confirmation\" must be a same as field \"password\".",
            'mailing_enabled.required' => "The field \"mailing_enabled\" is required.",
            'mailing_enabled.boolean' => "The field \"mailing_enabled\" must be boolean.",
            "terms_accepted.required" => "The field \"terms_accepted\" is required.",
            "terms_accepted.boolean" => "The field \"terms_accepted\" must be boolean.",
        ];
    }
}
