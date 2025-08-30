<?php

namespace App\Http\Requests\Api\V1\TopicRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="CreatingTopicRequest",
 *     title="Creating Topic Request (схема для создания топика)",
 *     description="Данные для создания новой темы",
 *     required={"name"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         example="История"
 *     )
 * )
 */
class CreatingTopicRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Topic name is required.',
            'name.string' => 'Topic name must be a string.',
            'name.max' => 'Topic name cannot be longer than 255 characters.',
        ];
    }
}
