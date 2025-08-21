<?php

namespace App\Http\Requests\Api\V1\UserRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NearByRequest extends FormRequest
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
            'radius'=>['required', 'integer', 'min:1', 'max:20000000'],
        ];
    }
    public function messages(): array
    {
        return [
            'radius.required' => "The field \"radius\" is required.",
            'radius.integer' => "The field \"radius\" must be an integer.",
            'radius.min' => "The minimum value of field \"radius\" is 1",
            'radius.max' => "The maximum value of field \"radius\" is 20000000",
        ];
    }
}
