<?php

namespace App\Http\Requests\Api\V1\AuthRequests;

use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', 'exists:password_reset_tokens,email'],
            'password' => 'required|string|confirmed|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
            'token' => 'required|string'
        ];
    }
    public function messages(): array
    {
        return [
            'email.required' => __('validation.email_required'),
            'email.string' =>  __('validation.email_string'),
            'email.email' => __('validation.email_email'),
            'email.max' => __('validation.email_max'),
            'email.exists' => __('validation.email_exists'),
            'password.required' => __('validation.password_required'),
            'password.string' => __('validation.password_string'),
            'password.confirmed' => __('validation.password_confirmed'),
            'password.min'=>__('validation.password_min'),
            'password.regex' => __('validation.password_regex'),
            'token.required' => __('validation.token_required'),
            'token.string' => __('validation.token_string')
        ];
    }
}
