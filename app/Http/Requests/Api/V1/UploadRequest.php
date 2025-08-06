<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UploadRequest",
 *     title="Upload Request (Загрузка файла)",
 *     description="Данные запроса для загрузки файла (максимальный размер 10 МБ).",
 *     type="object",
 *     required={"file"},
 *
 *     @OA\Property(
 *         property="file",
 *         type="string",
 *         format="binary",
 *         description="Файл для загрузки",
 *         example=null
 *     )
 * )
 */
class UploadRequest extends FormRequest
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
            'file' => ['required', 'file', 'max:10240']
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Пожалуйста, выберите файл для загрузки.',
            'file.file' => 'Загруженный элемент должен быть действительным файлом.',
            'file.max' => 'Размер файла не может превышать 10 МБ (10240 КБ).'
        ];
    }
}
