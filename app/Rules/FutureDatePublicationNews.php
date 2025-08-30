<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class FutureDatePublicationNews implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Если значение пустое, валидация проходит
        if (is_null($value)) {
            return;
        }
        try {
            $date = Carbon::parse($value); // Парсим значение в объект Carbon
        } catch (Exception) {
            $fail("Поле $attribute должно быть корректной датой.");
            return;
        }

        if ($date->isPast()) {
            $fail("Поле $attribute должно быть будущей датой.");
        }
    }
}
