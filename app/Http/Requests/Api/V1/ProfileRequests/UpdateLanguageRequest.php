<?php

namespace App\Http\Requests\Api\V1\ProfileRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLanguageRequest extends FormRequest
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
            "automatic" => ["required", "boolean"],
            'language_id'=>["required_if:automatic,0", "integer", "exists:languages,id"],
        ];
    }
    public function messages(): array
    {
        return [
            "automatic.required" => "The field \"automatic\" is required.",
            "automatic.boolean" => "The field \"automatic\" must be a boolean.",
            "language_id.required_if" => "The field \"language_id\" is required.",
            "language_id.integer" => "The field \"language_id\" must be an integer.",
            "language_id.exists" => "The field \"language_id\" is invalid.",
        ];
    }
}
