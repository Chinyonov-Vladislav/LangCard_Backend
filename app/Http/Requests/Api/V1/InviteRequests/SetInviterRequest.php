<?php

namespace App\Http\Requests\Api\V1\InviteRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="SetInviterRequest",
 *     title="Set Inviter Request (Установка пригласившего пользователя)",
 *     description="Запрос для установки пригласившего пользователя по коду приглашения. Код должен состоять из 16 символов: только заглавные латинские буквы и цифры (A–Z, 0–9).",
 *     type="object",
 *     required={"invite_code"},
 *     @OA\Property(
 *         property="invite_code",
 *         type="string",
 *         description="Пригласительный код. Должен содержать ровно 16 символов: заглавные латинские буквы и цифры.",
 *         example="AB12CD34EF56GH78",
 *         minLength=16,
 *         maxLength=16,
 *         pattern="^[A-Z0-9]{16}$"
 *     )
 * )
 */
class SetInviterRequest extends FormRequest
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
            'invite_code'=>['required', 'string', 'size:16', 'regex:/^[A-Z0-9]{16}$/']
        ];
    }
    public function messages(): array
    {
        return [
            'invite_code.required' => 'Пригласительный код обязателен для заполнения.',
            'invite_code.string'   => 'Пригласительный код должен быть строкой.',
            'invite_code.size'     => 'Пригласительный код должен содержать ровно 16 символов.',
            'invite_code.regex'    => 'Код должен состоять только из заглавных латинских букв и цифр (A–Z, 0–9).',
        ];
    }
}
