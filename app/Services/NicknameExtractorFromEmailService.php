<?php

namespace App\Services;

class NicknameExtractorFromEmailService
{
    public function extractNicknameFromEmail($email): false|string
    {
        // 1) Есть ли маркер #EXT# ?
        if (str_contains($email, '#EXT#')) {

            // 2) Берём левую часть до маркера
            [$leftPart] = explode('#EXT#', $email, 2);

            // 3) Последний «_» разделяет локальную часть и домен
            $lastUnderscore = strrpos($leftPart, '_');
            if ($lastUnderscore !== false) {
                return substr($leftPart, 0, $lastUnderscore); // john.doe / ivan.petrov
            }
            // Если почему-то нет «_», возвращаем всё, что до #EXT#
            return $leftPart;
        }
        // Обычный адрес – никнейм до «@»
        return explode('@', $email)[0];
    }
}
