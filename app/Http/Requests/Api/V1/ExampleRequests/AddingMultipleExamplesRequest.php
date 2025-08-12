<?php

namespace App\Http\Requests\Api\V1\ExampleRequests;

use Illuminate\Foundation\Http\FormRequest;

class AddingMultipleExamplesRequest extends FormRequest
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
            'examples' => ['required','array'],
            'examples.*.example' => ['required', 'string', 'max:255'],
            'examples.*.source' => ['required', 'string', 'in:original,target'],
        ];
    }
    public function messages(): array
    {
        return [

            'examples.required' => 'Массив примеров обязателен.',
            'examples.array' => 'Поле примеров должно быть массивом.',

            'examples.*.example.required' => 'Поле "example" в каждом примере обязательно.',
            'examples.*.example.string' => 'Поле "example" должно быть строкой.',
            'examples.*.example.max' => 'Поле "example" не должно превышать 255 символов.',

            'examples.*.source.required' => 'Поле "source" в каждом примере обязательно.',
            'examples.*.source.string' => 'Поле "source" должно быть строкой.',
            'examples.*.source.in' => 'Поле "source" должно иметь значение "original" или "target".',
        ];
    }
}
