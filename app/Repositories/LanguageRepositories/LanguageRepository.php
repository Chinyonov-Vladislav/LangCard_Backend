<?php

namespace App\Repositories\LanguageRepositories;

use App\Models\Language;

class LanguageRepository implements LanguageRepositoryInterface
{
    protected Language $model;

    public function __construct(Language $model)
    {
        $this->model = $model;
    }

    public function getAllLanguagesName()
    {
        return $this->model->select(['id','name'])->get()->toArray();
    }

    public function isExistLanguageByLocale(string $languageLocale)
    {
        return $this->model->where('locale','=', $languageLocale)->exists();
    }

    public function saveLanguage(string $languageName,string $native_name, string $languageCode,string $locale, string $urlToImage): Language
    {
        $newLanguage = new Language();
        $newLanguage->name = $languageName;
        $newLanguage->native_name = $native_name;
        $newLanguage->code = $languageCode;
        $newLanguage->locale = $locale;
        $newLanguage->flag_url = $urlToImage;
        $newLanguage->save();
        return $newLanguage;
    }

    public function getAllIdLanguages()
    {
        return $this->model->select(['id'])->get()->toArray();
    }

    public function isExistLanguageById(int $languageId)
    {
        return $this->model->where('id', '=', $languageId)->exists();
    }

    public function getAllLanguages()
    {
        return $this->model->get();
    }
    public function getExistentLocale(array $locales)
    {
        return Language::whereIn('locale', $locales)
            ->pluck('locale')
            ->toArray();
    }

    public function getLanguageByLocale(string $locale): ?Language
    {
        return $this->model->where('locale', '=', $locale)->first();
    }
}
