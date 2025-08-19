<?php

namespace App\Http\Requests\Api\V1\ChatRequests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatingMessageRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "message_text"=>["required", "string"]
        ];
    }
    public function messages(): array
    {
        return [
            "message_text.required"=>"Message text is required",
            "message_text.string"=>"Message text must be a string"
        ];
    }
}
