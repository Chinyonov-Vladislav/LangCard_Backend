<?php

namespace App\Services;

class GeneratingPasswordService
{
    public function generatePassword(): string
    {
        $length = random_int(8, 16);
        // Наборы символов
        $lower   = 'abcdefghijklmnopqrstuvwxyz';
        $upper   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits  = '0123456789';
        $special = '@$!%*?&';

        // Обязательные символы (по одному из каждого набора)
        $password = [
            $lower[random_int(0, strlen($lower) - 1)],
            $upper[random_int(0, strlen($upper) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $special[random_int(0, strlen($special) - 1)],
        ];

        // Объединяем все символы для добора длины
        $all = $lower . $upper . $digits . $special;

        // Добавляем недостающие символы
        while (count($password) < $length) {
            $password[] = $all[random_int(0, strlen($all) - 1)];
        }

        // Перемешиваем для случайного порядка
        shuffle($password);

        return implode('', $password);
    }
}
