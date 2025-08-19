<?php

namespace App\Http\Requests\Api\V1\ChatRequests\InvitesInChatsRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ResponseUserToRequestOrInvitationToGroupChatRequest extends FormRequest
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
            "response_user"=>['required', 'boolean'],
        ];
    }
    public function messages(): array
    {
        return [
            "response_user.required" => "The field \"response_user\" is required.",
            "response_user.boolean" => "The field \"response_user\" must be a boolean.",
        ];
    }
}
