<?php

namespace App\Http\Requests\Api\V1\NewsRequests;

use App\Rules\FutureDatePublicationNews;
use App\Rules\FilePathExistsRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateNewsRequest extends FormRequest
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
            'title' => ["required","string","max:255"],
            'main_image' => ["nullable","string", new FilePathExistsRule()], // URL загруженной картинки
            'content_news' => ["required", "string"],
            'published_at' => ['nullable', 'date', 'date_format:Y-m-d H:i:s', new FutureDatePublicationNews()],
        ];
    }

    public function messages(): array
    {
        return [
            'title.string' => "The field \"title\" must be a string.",
            'title.required' => "The field \"title\" is required.",
            'title.max' => "The max length of field \"title\" is 255 characters.",
            'main_image.string' => "The field \"main_image\" must be a string.",
            'content_news.string' => "The field \"content\" must be an string.",
            'content_news.required' => "The \"content\" field is required.",
            'published_at.date' => "The field \"published_at\" must be a date.",
            'published_at.date_format' => "The field \"published_at\" must be a date format: Y-m-d H:i:s",
        ];
    }
}
