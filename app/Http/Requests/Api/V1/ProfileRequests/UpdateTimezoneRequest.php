<?php

namespace App\Http\Requests\Api\V1\ProfileRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTimezoneRequest extends FormRequest
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
            'timezone_id'=>["required_if:automatic,0", "integer", "exists:timezones,id"],
        ];
    }

    public function messages(): array
    {
        return [
            "automatic.required" => "The field \"automatic\" is required.",
            "automatic.boolean" => "The field \"automatic\" must be a boolean.",
            "timezone_id.required_if" => "The field \"timezone_id\" is required.",
            "timezone_id.integer" => "The field \"timezone_id\" must be an integer.",
            "timezone_id.exists" => "The field \"timezone_id\" is invalid.",
        ];
    }
}
