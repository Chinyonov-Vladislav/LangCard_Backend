<?php

namespace App\Http\Requests\Api\V1\ChatRequests;

use Illuminate\Foundation\Http\FormRequest;

class CreatingDirectCharRequest extends FormRequest
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
            "second_user_id"=>["required", "integer", "exists:users,id"]
        ];
    }

    public function messages(): array
    {
        return [
            "second_user_id.required" => "Id второго пользователя является обязательным",
            "second_user_id.integer" => "Id второго пользователя должен быть целым числом",
            "second_user_id.exists" => "Id второго пользователя не существует"
        ];
    }
}
