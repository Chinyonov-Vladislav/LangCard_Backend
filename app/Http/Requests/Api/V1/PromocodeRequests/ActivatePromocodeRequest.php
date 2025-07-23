<?php

namespace App\Http\Requests\Api\V1\PromocodeRequests;

use Illuminate\Foundation\Http\FormRequest;

class ActivatePromocodeRequest extends FormRequest
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
            'code'=>['required', 'string', 'size:19', 'regex:/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', 'exists:promocodes,code'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Поле код обязательно для заполнения',
            'code.string' => 'Код должен быть строкой',
            'code.size' => 'Код должен содержать 19 символов',
            'code.regex' => 'Код должен быть в формате XXXX-XXXX-XXXX-XXXX (заглавные буквы и цифры)',
            'code.exists' => 'Предоставленный код отсутствует'
        ];
    }
}
