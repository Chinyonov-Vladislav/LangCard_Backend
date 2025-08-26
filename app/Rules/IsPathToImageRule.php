<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsPathToImageRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($value)) {
            return;
        }
        $cleanPath = str_starts_with($value, 'storage/')
            ? substr($value, 8)
            : $value;
        $allowedExtensions = ['jpg','jpeg','png','gif','webp','svg','bmp','tiff','ico'];
        $extension = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));

        if (!str_starts_with($cleanPath, 'images/') || !in_array($extension, $allowedExtensions)) {
            $fail('Путь указывает на файл, который не является изображением.');
        }
    }
}
