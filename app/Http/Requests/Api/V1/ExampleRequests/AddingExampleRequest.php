<?php

namespace App\Http\Requests\Api\V1\ExampleRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AddingExampleRequest extends FormRequest
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
            'example' => ['required', 'string', 'max:255'],
            'source'=>['required', 'string', 'in:original,target']
        ];
    }

    public function messages(): array
    {
        return [

            'example.required' => 'Поле example обязательно для заполнения.',
            'example.string' => 'Поле example должно быть строкой.',
            'example.max' => 'Поле example не должно превышать 255 символов.',

            'source.required' => 'Поле source обязательно для заполнения.',
            'source.string' => 'Поле source должно быть строкой.',
            'source.in' => 'Поле source должно принимать одно из значений: original, target.'
        ];
    }
}
