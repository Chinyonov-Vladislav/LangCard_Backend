<?php

namespace App\Http\Requests\Api\V1\TwoFactorAuthorizationRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Sabberworm\CSS\Rule\Rule;

class EnableDisableATwoFactorAuthorizationRequest extends FormRequest
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
            'type'=>['required', 'string', 'in:email,googleAuthenticator'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Поле типа обязательно для заполнения.',
            'type.string' => 'Поле типа должно быть строкой.',
            'type.in' => 'Поле типа должно быть одним из следующих значений: email или googleAuthenticator.',
        ];
    }
}
