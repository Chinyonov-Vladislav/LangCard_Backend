<?php

namespace App\Helpers;

class ValidationExampleUsageTranslatedWord
{
    public function validateTranslatedWord(string $sentence, string $translatedWord): bool
    {
        // Приводим искомое слово к нижнему регистру
        $target = mb_strtolower($translatedWord, 'UTF-8');

        // Разбиваем строку на слова
        $words = preg_split('/\s+/', trim($sentence));

        foreach ($words as $w) {
            // Убираем пунктуацию и приводим к нижнему регистру
            $clean = mb_strtolower(trim($w), 'UTF-8');
            $clean = preg_replace('/[^\p{L}\p{N}]/u', '', $clean); // только буквы/цифры

            if ($clean === $target) {
                return true;
            }
        }

        return false;
    }
}
