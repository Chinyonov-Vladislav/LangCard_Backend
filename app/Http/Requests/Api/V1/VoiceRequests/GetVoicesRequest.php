<?php

namespace App\Http\Requests\Api\V1\VoiceRequests;

use Illuminate\Foundation\Http\FormRequest;

class GetVoicesRequest extends FormRequest
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
            'page' => ['nullable', 'integer', 'min:1',],
            'countOnPage' => ['nullable','integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            // Валидация страницы
            'page.integer' => 'Номер страницы должен быть целым числом.',
            'page.min' => 'Номер страницы не может быть меньше 1.',

            // Валидация количества элементов на странице
            'countOnPage.integer' => 'Количество элементов на странице должно быть целым числом.',
            'countOnPage.min' => 'Количество элементов на странице не может быть меньше 1.'
        ];
    }
}
