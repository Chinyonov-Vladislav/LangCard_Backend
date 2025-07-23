<?php

namespace App\Helpers;

class ValidationInsertedWordInTest
{
    // проверяет есть ли $insertedWord в $sentence как отдельное слово без учёта регистра
    public function isExistInsertedWordInSentence(string $sentence, string $insertedWord): bool
    {
        // Удаляем лишние пробелы и разбиваем строку на слова
        $words = preg_split('/\s+/', trim($sentence));

        // Проверяем:
        // 1. Есть ли слово в строке как отдельное (с границами слова)
        // 2. Количество слов в строке > 1 (слово не единственное)
        return preg_match("/\b" . preg_quote($insertedWord, '/') . "\b/i", $sentence) && count($words) > 1;
    }
}
