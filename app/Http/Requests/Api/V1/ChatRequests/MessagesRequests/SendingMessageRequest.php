<?php

namespace App\Http\Requests\Api\V1\ChatRequests\MessagesRequests;

use App\Rules\FilePathExistsRule;
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
            "message"=>["nullable", "string"],
            "paths_to_files"   => ['nullable', 'array'],
            "paths_to_files.*" => ['required_with:files', 'string', new FilePathExistsRule()],
        ];
    }

    public function messages(): array
    {
        return [
            'message.string' => 'The message must be a string.',
            'paths_to_files.array' => "The field \"files\" must be an array.",
            'paths_to_files.*.required_with' => "The item of array \"files\" is required.",
            'paths_to_files.*.string' => "The item of array \"files\" must be a string.",
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->filled('message') && !$this->filled('paths_to_files')) {
                $validator->errors()->add('message', 'Message or files are required.');
            }
        });
    }
}
