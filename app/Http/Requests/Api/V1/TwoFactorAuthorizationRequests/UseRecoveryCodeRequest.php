<?php

namespace App\Http\Requests\Api\V1\TwoFactorAuthorizationRequests;

use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
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
