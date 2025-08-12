<?php

namespace App\Http\Requests\Api\V1\CardRequests;

use Illuminate\Foundation\Http\FormRequest;

class AddingVoiceForCardRequest extends FormRequest
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
            'originalVoices' => ['sometimes','array'],
            'originalVoices.*' => ['required', 'string', 'exists:voices,voice_id'],
            'targetVoices' => ['sometimes','array'],
            'targetVoices.*' => ['required','string', 'exists:voices,voice_id'],
        ];
    }

    public function messages(): array
    {
        return [
            'originalVoices.array' => 'Поле "Оригинальные голоса" должно быть массивом.',
            'originalVoices.*.required' => 'Каждый элемент в "Оригинальные голоса" обязателен.',
            'originalVoices.*.string' => 'Каждый элемент в "Оригинальные голоса" должен быть строкой.',
            'originalVoices.*.exists' => 'Выбранный оригинальный голос не существует.',

            'targetVoices.array' => 'Поле "Целевые голоса" должно быть массивом.',
            'targetVoices.*.required' => 'Каждый элемент в "Целевые голоса" обязателен.',
            'targetVoices.*.string' => 'Каждый элемент в "Целевые голоса" должен быть строкой.',
            'targetVoices.*.exists' => 'Выбранный целевой голос не существует.',

            'examples.array' => 'Поле "Примеры" должно быть массивом.',
            'examples.*.required' => 'Каждый пример обязателен для заполнения.',
            'examples.*.string' => 'Каждый пример должен быть строкой.',
        ];
    }
}
