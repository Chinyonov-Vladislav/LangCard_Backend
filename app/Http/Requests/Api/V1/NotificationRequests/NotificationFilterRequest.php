<?php

namespace App\Http\Requests\Api\V1\NotificationRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NotificationFilterRequest extends FormRequest
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
            'page' => ['sometimes', 'integer', 'min:1'],
            'countOnPage' => ['sometimes', 'integer', 'min:1'],
            "onlyUnread" =>["sometimes", "boolean"],
            "orderDirection" =>['sometimes', 'string', "in:asc,desc"],
        ];
    }

    public function messages(): array
    {
        return [
            "page.integer" => "Page number must be an integer",
            "page.min" => "Page number must be a positive number",
            "countOnPage.integer" => "Count number must be an integer",
            "countOnPage.min" => "Count number must be a positive number",
            "onlyUnread.boolean" => "Only unread notifications must be boolean",
            "orderDirection.string" => "Order direction must be a string",
            "orderDirection.in" => "Order direction can be only asc or desc",
        ];
    }
}
