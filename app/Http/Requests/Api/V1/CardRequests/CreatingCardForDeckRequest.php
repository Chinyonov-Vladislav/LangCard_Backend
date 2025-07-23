<?php

namespace App\Http\Requests\Api\V1\CardRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreatingCardForDeckRequest extends FormRequest
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
            'deck_id' => ['required', 'integer', 'exists:decks,id'],
            'word' => ['required', 'string', 'max:255'],
            'translate' => ['required', 'string', 'max:255'],
            'image' => [
                'exclude_if:image,null',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120', // 5MB
            ],
            'originalVoices' => ['nullable','string'],
            'targetVoices' => ['nullable', 'string'],
        ];
    }
    public function messages(): array
    {
        return [
            // deck_id
            'deck_id.required' => 'Необходимо выбрать колоду для карточки.',
            'deck_id.integer' => 'Идентификатор колоды должен быть числом.',
            'deck_id.exists' => 'Выбранная колода не существует.',

            // word
            'word.required' => 'Поле "Слово" обязательно для заполнения.',
            'word.string' => 'Поле "Слово" должно содержать текст.',
            'word.max' => 'Поле "Слово" не должно содержать более 255 символов.',

            // translate
            'translate.required' => 'Поле "Перевод" обязательно для заполнения.',
            'translate.string' => 'Поле "Перевод" должно содержать текст.',
            'translate.max' => 'Поле "Перевод" не должно содержать более 255 символов.',

            // image
            'image.image' => 'Загружаемый файл должен быть изображением',
            'image.mimes' => 'Изображение должно быть в формате: jpeg, png, jpg, gif или webp.',
            'image.max' => 'Размер изображения не должен превышать 5 МБ.',

            'originalVoices'=>"Поле originalVoices должно быть строкой",
            'targetVoices'=>"Поле originalVoices должно быть строкой"
        ];
    }
}
