<?php

namespace App\Http\Requests\Api\V1\ChatRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreatingGroupChatRequest extends FormRequest
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
            "name"=>['required', 'string', 'min:3', 'max:255'],
            "is_private"=>['required', 'boolean']
        ];
    }

    public function messages(): array
    {
        return [
            "name.required"=>"Field \"name\" is required",
            "name.min"=>"Field \"name\" must be at least 3 characters",
            "name.max"=>"Field \"name\" may not be greater than 255 characters",
            "name.string" => "Field \"name\" must be a string",
            "is_private.required"=>"Field \"is_private\" is required",
            "is_private.boolean" => "Field \"is_private\" is required",
        ];
    }
}
