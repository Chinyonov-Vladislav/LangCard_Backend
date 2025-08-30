<?php

namespace App\Http\Requests\Api\V1\PromocodeRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ActivatePromocodeRequest",
 *     title="Activate Promocode Request (Активация промокода)",
 *     description="Схема запроса для активации промокода. Код должен состоять из 19 символов, разделённых дефисами, и быть в формате XXXX-XXXX-XXXX-XXXX (заглавные латинские буквы и цифры).",
 *     type="object",
 *     required={"code"},
 *
 *     @OA\Property(
 *         property="code",
 *         type="string",
 *         description="Промокод в формате XXXX-XXXX-XXXX-XXXX. Только заглавные буквы и цифры. Пример: A1B2-C3D4-E5F6-G7H8",
 *         example="AB12-CD34-EF56-GH78",
 *         minLength=19,
 *         maxLength=19,
 *         pattern="^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$"
 *     )
 * )
 */
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
     * @return array<string, ValidationRule|array|string>
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
