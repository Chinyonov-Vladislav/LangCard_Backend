<?php

namespace App\Http\Requests\Api\V1\ChatRequests\InvitesInChatsRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SendInviteToChatRequest extends FormRequest
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
            "user_id"=>['required','integer','exists:users,id']
        ];
    }

    public function messages(): array
    {
        return [
            "user_id.required" => "Field \"user_id\" is required",
            "user_id.integer" => "Field \"user_id\" must be an integer",
            "user_id.exists" => "User doesn't exist",
        ];
    }
}
