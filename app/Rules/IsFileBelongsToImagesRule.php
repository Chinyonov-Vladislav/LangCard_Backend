<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Translation\PotentiallyTranslatedString;

class IsFileBelongsToImagesRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($value)) {
            return;
        }
        $cleanPath = str_starts_with($value, 'storage/')
            ? substr($value, 8) // убираем 'storage/'
            : $value;
        $fullPath = Storage::disk('public')->path($cleanPath);
        $mimeType = mime_content_type($fullPath);

        $allowedImageMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'image/svg+xml', 'image/bmp', 'image/tiff', 'image/ico'
        ];

        if (!in_array($mimeType, $allowedImageMimes)) {
            $fail('Файл не является поддерживаемым изображением.');
        }
    }
}
