<?php

namespace App\Http\Requests\Api\V1\ChatRequests\MessagesRequests;

use App\Rules\FilePathExistsRule;
use Illuminate\Contracts\Validation\ValidationRule;
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            "message" => ["nullable", "string"],
            "attachments" => ['nullable', 'array'],
            "attachments.*.path" => ['required_with:files', 'string', new FilePathExistsRule()],
            "attachments.*.action" => ['required_with:files', 'string', 'in:add,delete, nothing'],
        ];
    }

    public function messages(): array
    {
        return [
            // message
            "message.string" => "The message must be a string.",

            // paths_to_files
            "attachments.array" => "The field \"attachments\" must be an array.",

            // paths_to_files.*.path
            "attachments.*.path.required_with" => "Each item in \"attachments\" must contain a file path when files are provided.",
            "attachments.*.path.string" => "The \"path\" value in each item of \"attachments\" must be a string.",

            // paths_to_files.*.action
            "attachments.*.action.required_with" => "Each item in \"attachments\" must contain an action.",
            "attachments.*.action.string" => "The \"action\" value in each item of \"attachments\" must be a string.",
            "attachments.*.action.in" => "The \"action\" field must be one of the following values: add or delete.",
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->filled('message') && !$this->filled('paths_to_files')) {
                $validator->errors()->add('message', 'Message or files are required.');
            }
        });
    }
}
