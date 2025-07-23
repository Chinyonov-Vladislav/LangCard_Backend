<?php

namespace App\Rules;

use App\Repositories\LanguageRepositories\LanguageRepositoryInterface;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class LanguageCodesExistRule implements ValidationRule
{
    protected LanguageRepositoryInterface $languageRepository;
    public function __construct(LanguageRepositoryInterface $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    /**
     * Run the validation rule.
     *
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Если значение пустое, валидация проходит
        if (empty($value)) {
            return;
        }
        // Преобразуем строку в массив кодов языков
        $languagesLocales = $this->parseLanguagesLocalesFromString($value);
        // Если после обработки массив пуст, валидация проходит
        if (empty($languagesLocales)) {
            return;
        }
        $missingCodes = array_values(array_diff($languagesLocales, $this->languageRepository->getExistentLocale($languagesLocales)));
        // Если есть отсутствующие коды, валидация не проходит
        if (!empty($missingCodes)) {
            $fail($this->buildErrorMessage($missingCodes));
        }
    }
    /**
     * Преобразует строку с кодами языков в массив
     */
    private function parseLanguagesLocalesFromString(string $value): array
    {
        // Разбиваем по запятой, убираем пробелы, удаляем пустые элементы
        return array_filter(
            array_map('trim', explode(',', $value)),
            fn($code) => !empty($code)
        );
    }

    /**
     * Формирует сообщение об ошибке
     */
    private function buildErrorMessage(array $missingCodes): string
    {
        if (count($missingCodes) === 1) {
            return "Код языка '{$missingCodes[0]}' не найден в базе данных.";
        }

        return 'Коды языков не найдены в базе данных: ' . implode(', ', $missingCodes) . '.';
    }
}
