<?php

namespace App\Http\Requests\Api\V1\InviteRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SetInviterRequest extends FormRequest
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
            'invite_code'=>['required', 'string', 'size:16', 'regex:/^[A-Z0-9]{16}$/']
        ];
    }
    public function messages(): array
    {
        return [
            'invite_code.required' => 'Пригласительный код обязателен для заполнения.',
            'invite_code.string'   => 'Пригласительный код должен быть строкой.',
            'invite_code.size'     => 'Пригласительный код должен содержать ровно 16 символов.',
            'invite_code.regex'    => 'Код должен состоять только из заглавных латинских букв и цифр (A–Z, 0–9).',
        ];
    }
}
