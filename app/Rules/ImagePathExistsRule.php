<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Translation\PotentiallyTranslatedString;

class ImagePathExistsRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($value)) {
            return;
        }
        $cleanPath = str_starts_with($value, 'storage/')
            ? substr($value, 8) // убираем 'storage/'
            : $value;
        if (!Storage::disk('public')->exists($cleanPath)) {
            $fail('Изображение не найдено на сервере.');
        }
    }
}
