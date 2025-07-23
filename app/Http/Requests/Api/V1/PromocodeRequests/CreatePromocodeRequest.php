<?php

namespace App\Http\Requests\Api\V1\PromocodeRequests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePromocodeRequest extends FormRequest
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
            'count' => ['required', 'int', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'count.required' => 'Поле \'count\' обязательно для заполнения',
            'count.int' => 'Поле \'count\' должно быть целым числом',
            'count.min' => 'Поле \'count\' должно быть положительным числом (минимальное значение = 1)',
        ];
    }
}
