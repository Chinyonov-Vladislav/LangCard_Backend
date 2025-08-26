<?php

namespace App\Http\Requests\Api\V1\ChatRequests\MessagesRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MessageChatFilterRequest extends FormRequest
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
            'last_message_id' => ["nullable", "integer", "exists:messages,id"],
            'limit' => ["sometimes","integer","min:1","max:50"]
        ];
    }
    public function messages(): array
    {
        return [
            "last_message_id.integer" => "The field \"last_message_id\" must be an integer.",
            "last_message_id.exists" => "The field \"last_message_id\" must be exists.",
            "limit.integer" => "The field \"limit\" must be an integer.",
            "limit.exists" => "The field \"limit\" must be exists.",
        ];
    }
}
