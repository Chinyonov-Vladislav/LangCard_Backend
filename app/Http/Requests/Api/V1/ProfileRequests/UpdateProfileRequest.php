<?php

namespace App\Http\Requests\Api\V1\ProfileRequests;

use App\Rules\FilePathExistsRule;
use App\Rules\IsPathToImageRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name'=>['required', 'string', 'max:255'],
            'avatar_url'=>['nullable', 'string', new FilePathExistsRule(), new IsPathToImageRule()]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => "The field \"name\" is required.",
            'name.string' => "The field \"name\" must be a string.",
            'name.max' => "The field \"name\" may not be greater than 255 characters.",
            'avatar_url.string' => "The field \"avatar_url\" must be a string.",
        ];
    }
}
