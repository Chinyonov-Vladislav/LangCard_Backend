<?php

namespace App\Http\Requests\Api\V1\LanguageRequests;

use App\Rules\ImagePathExistsRule;
use App\Rules\IsFileBelongsToImagesRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="AddingLanguageRequest",
 *     title="Adding Language Request (Добавление нового языка)",
 *     description="Схема запроса на добавление нового языка в систему. Все поля обязательны.",
 *     type="object",
 *     required={"name", "native_name", "code", "flag", "locale_lang"},
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Название языка на целевом языке (например, 'English')",
 *         example="English"
 *     ),
 *
 *     @OA\Property(
 *         property="native_name",
 *         type="string",
 *         description="Название языка на родном языке (например, 'Deutsch' для немецкого)",
 *         example="English"
 *     ),
 *
 *     @OA\Property(
 *         property="code",
 *         type="string",
 *         description="Двухсимвольный код языка по ISO 639-1",
 *         example="en",
 *         minLength=2,
 *         maxLength=2,
 *         pattern="^[a-z]{2}$"
 *     ),
 *
 *     @OA\Property(
 *         property="flag",
 *         type="string",
 *         description="Путь к изображению флага (проверяется существование и принадлежность к каталогу изображений)",
 *         example="flags/en.png"
 *     ),
 *
 *     @OA\Property(
 *         property="locale_lang",
 *         type="string",
 *         description="Уникальный код локали (например, 'en_US', должен быть уникальным в таблице `languages` по полю `locale`)",
 *         example="en_US"
 *     )
 * )
 */
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
            'name' => ['required', 'string'],
            'native_name' => ['required', 'string'],
            'code' => ['required', 'string', 'size:2'],
            'flag' => ['required',
                'string',
                new ImagePathExistsRule(),
                new IsFileBelongsToImagesRule()],
            'locale_lang'=> ['required', 'string', 'unique:languages,locale']
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
