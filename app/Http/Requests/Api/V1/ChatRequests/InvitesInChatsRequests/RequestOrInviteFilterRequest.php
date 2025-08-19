<?php

namespace App\Http\Requests\Api\V1\ChatRequests\InvitesInChatsRequests;

use Illuminate\Foundation\Http\FormRequest;

class RequestOrInviteFilterRequest extends FormRequest
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
            'page' => ['sometimes', 'integer', 'min:1'],
            'countOnPage' => ['sometimes', 'integer', 'min:1'],
            'sortDirection' => ['sometimes', 'string', 'in:asc,desc'],
        ];
    }

    public function messages(): array
    {
        return [
            'page.integer' => "The query parameter \"page\" must be an integer.",
            "page.min" => "The query parameter \"page\" must be greater than 0.",
            "countOnPage.integer" => "The query parameter \"countOnPage\" must be an integer.",
            "countOnPage.min" => "The query parameter \"countOnPage\" must be greater than 0.",
            "sortDirection.string" => "The query parameter \"sortDirection\" must be a string.",
            "sortDirection.in" => "The query parameter \"sortDirection\" can be asc or desc.",
        ];
    }
}
