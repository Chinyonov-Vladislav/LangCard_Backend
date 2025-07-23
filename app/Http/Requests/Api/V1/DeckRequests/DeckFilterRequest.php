<?php

namespace App\Http\Requests\Api\V1\DeckRequests;

use App\Enums\TypeShowingDeck;
use App\Repositories\LanguageRepositories\LanguageRepositoryInterface;
use App\Rules\LanguageCodesExistRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeckFilterRequest extends FormRequest
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
            'page' => ['nullable', 'integer', 'min:1'],
            'countOnPage' => ['nullable', 'integer', 'min:1'],
            'originalLanguages' => ['nullable', 'string', new LanguageCodesExistRule(app(LanguageRepositoryInterface::class))],
            'targetLanguages' => ['nullable', 'string', new LanguageCodesExistRule(app(LanguageRepositoryInterface::class))],
            'showPremium' => [
                'nullable',
                'string',
                Rule::in(TypeShowingDeck::getValues())
            ],
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
            'countOnPage.min' => 'Количество элементов на странице не может быть меньше 1.',

            // Валидация оригинальных языков
            'originalLanguages.string' => 'Оригинальные языки должны быть переданы в виде строки.',

            // Валидация целевых языков
            'targetLanguages.string' => 'Целевые языки должны быть переданы в виде строки.',

            // Валидация типа показа премиум контента
            'showPremium.string' => 'Тип показа премиум контента должен быть строкой.',
            'showPremium.in' => 'Недопустимое значение для типа показа премиум контента. Допустимые значения: :values.',
        ];
    }
}
