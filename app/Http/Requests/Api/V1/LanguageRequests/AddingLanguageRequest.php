<?php

namespace App\Http\Requests\Api\V1\LanguageRequests;

use App\Rules\ImagePathExistsRule;
use App\Rules\IsFileBelongsToImagesRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AddingLanguageRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'native_name' => 'required|string',
            'code' => 'required|string|size:2',
            'flag' => ['required',
                'string',
                new ImagePathExistsRule(),
                new IsFileBelongsToImagesRule()],
            'locale_lang'=> 'required|string|unique:languages,locale'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Поле "Название" обязательно для заполнения.',
            'name.string' => 'Поле "Название" должно быть строкой.',

            'native_name.required' => 'Поле "Родное название" обязательно для заполнения.',
            'native_name.string' => 'Поле "Родное название" должно быть строкой.',

            'code.required' => 'Поле "Код языка" обязательно для заполнения.',
            'code.string' => 'Поле "Код языка" должно быть строкой.',
            'code.size' => 'Поле "Код языка" должно содержать ровно 2 символа.',

            'flag.required' => 'Поле "Флаг" обязательно для заполнения.',
            'flag.string' => 'Поле "Флаг" должно быть строкой.',

            'locale.required' => 'Поле "Локаль" обязательно для заполнения.',
            'locale.string' => 'Поле "Локаль" должно быть строкой.',
            'locale.unique' => 'Такая локаль уже существует в системе. Выберите другую локаль.',
        ];
    }
}
