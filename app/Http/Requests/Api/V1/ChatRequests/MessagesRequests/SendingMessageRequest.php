<?php

namespace App\Http\Requests\Api\V1\ChatRequests\MessagesRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SendingMessageRequest extends FormRequest
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
            "message"=>["required", "string"],
            "emotions"=>['sometimes', "array"],
            "emotions.*"=>["required", "integer", "exists:emotions,id"]
        ];
    }

    public function messages(): array
    {
        return [
            'message.required'    => 'The message field is required.',
            'message.string'      => 'The message must be a string.',
            'emotions.array'      => 'The emotions field must be an array.',
            'emotions.*.required' => 'Each emotion is required.',
            'emotions.*.integer'   => 'Each emotion must be a integer.',
            'emotions.*.exists'   => 'The selected emotion does not exist.',
        ];
    }

}
