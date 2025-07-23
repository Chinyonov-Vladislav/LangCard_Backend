<?php

namespace App\Repositories\LanguageRepositories;

use App\Models\Language;

interface LanguageRepositoryInterface
{
    public function getAllLanguages();
    public function getAllIdLanguages();

    public function getAllLanguagesName();

    public function getExistentLocale(array $locales);

    public function getLanguageByLocale(string $locale): ?Language;

    public function isExistLanguageByLocale(string $languageLocale);

    public function isExistLanguageById(int $languageId);


    public function saveLanguage(string $languageName,string $native_name, string $languageCode,string $locale, string $urlToImage);
}
